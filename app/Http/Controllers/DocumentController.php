<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentType;
use Carbon\Carbon;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;


class DocumentController extends Controller
{
    protected $elasticClient;

    public function __construct()
    {
        // Récupère la première configuration
        $config = config('elasticsearch.hosts')[0];

        // Construit une URL pour le host
        if (!empty($config['user']) && !empty($config['pass'])) {
            $hostString = sprintf(
                "%s://%s:%s@%s:%s",
                $config['scheme'],
                $config['user'],
                $config['pass'],
                $config['host'],
                $config['port']
            );
        } else {
            $hostString = sprintf(
                "%s://%s:%s",
                $config['scheme'],
                $config['host'],
                $config['port']
            );
        }

        // Initialisation du client Elasticsearch en passant un tableau de chaînes
        $this->elasticClient = ClientBuilder::create()
            ->setHosts([$hostString])
            ->setRetries($config['retries'])
            ->build();

        try {
            $this->elasticClient->indices()->get(['index' => 'documents']);
        } catch (ElasticsearchException $e) {
            if ($e->getCode() === 404) {
                $this->createElasticsearchIndex($config['analyzer']);
            } else {
                Log::error("Erreur Elasticsearch : " . $e->getMessage());
            }
        }
    }

    private function createElasticsearchIndex($analyzer = 'french')
    {
        $params = [
            'index' => 'documents',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'default' => [
                                'type' => $analyzer
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'doc_id' => ['type' => 'keyword'],
                        'doc_name' => [
                            'type' => 'text',
                            'analyzer' => $analyzer,
                            'fields' => [
                                'keyword' => ['type' => 'keyword']
                            ]
                        ],
                        'doc_type' => ['type' => 'keyword'],
                        'doc_content' => [
                            'type' => 'text',
                            'analyzer' => $analyzer
                        ],
                        'doc_format' => ['type' => 'keyword'],
                        'doc_insert_date' => ['type' => 'date'],
                        'doc_updated_date' => ['type' => 'date']
                    ]
                ]
            ]
        ];

        $this->elasticClient->indices()->create($params);
    }

    // Afficher la liste des documents et le formulaire de recherche
    public function index()
    {
        // Récupération des documents depuis MongoDB (si nécessaire pour l’interface)
        $documents = Document::all();
        $docTypes = DocumentType::pluck('name', 'name')->toArray();

        return view('documents.index', compact('documents', 'docTypes'));
    }

    // Afficher le formulaire de création d’un document
    public function create()
    {
        $docTypes = DocumentType::pluck('name', 'name')->toArray();

        return view('documents.create', compact('docTypes'));
    }

    // Stocker un nouveau document
    public function store(Request $request)
    {
        $request->validate([
            'doc_id'        => 'unique:documents,doc_id',
            'doc_name'      => 'required|string|max:255',
            'doc_type'      => 'required|string|max:100',
            'doc_file'      => 'required|file|mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        // Vérification que le type existe
        if (!DocumentType::where('name', $request->doc_type)->exists()) {
            return redirect()->back()->withErrors(['doc_type' => 'Type de document invalide']);
        }

        // Génération de l'ID et stockage du fichier
        $docId = Str::uuid()->toString();
        $file = $request->file('doc_file');
        $fileName = $docId . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        // Détermination automatique du format
        $docFormat = strtolower($file->getClientOriginalExtension()) === 'pdf' ? 'pdf' : 'word';

        // Extraction automatique du contenu
        $docContent = $this->extractTextFromFile($file->getRealPath(), $docFormat);
        if (!$docContent) {
            return back()->withErrors(['doc_file' => 'Échec de l\'extraction du texte']);
        }

        // Création du document
        $document = Document::create([
            'doc_id'             => $docId,
            'doc_name'           => $request->doc_name,
            'doc_type'           => $request->doc_type,
            'doc_content'        => $docContent,  // Texte extrait automatiquement
            'doc_format'         => $docFormat,   // Format déterminé automatiquement
            'doc_insert_date'    => Carbon::now(),
            'doc_updated_date'   => Carbon::now(),
            'doc_file_full_path' => $filePath,    // Chemin relatif seulement
        ]);

        $this->indexDocumentInElastic($document);

        return redirect()->route('documents.index')->with('success', 'Document créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'un document.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $document = Document::findOrFail($id);
        $docTypes = DocumentType::pluck('name', 'name')->toArray();

        return view('documents.edit', compact('document', 'docTypes'));
    }

    // Mise à jour d'un document
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $request->validate([
            'doc_id' => 'unique:documents,doc_id,' . $document->doc_id,
            'doc_name' => 'required|string|max:255',
            'doc_type' => 'required|string|max:100',
            'doc_file' => 'nullable|file|mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        // Vérification que le type existe
        if (!DocumentType::where('name', $request->doc_type)->exists()) {
            return redirect()->back()->withErrors(['doc_type' => 'Type de document invalide']);
        }

        // Traitement du fichier si uploadé
        if ($request->hasFile('doc_file')) {
            // Supprimer l'ancien fichier
            Storage::disk('public')->delete($document->doc_file_full_path);

            // Stocker le nouveau fichier
            $file = $request->file('doc_file');
            $fileName = $document->doc_id . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');
            $document->doc_file_full_path = $filePath;

            // Extraire le contenu et déterminer le format
            $docContent = $this->extractTextFromFile($file->getRealPath(), strtolower($file->getClientOriginalExtension()) === 'pdf' ? 'pdf' : 'word');
            if (!$docContent) {
                return back()->withErrors(['doc_file' => 'Échec de l\'extraction du texte']);
            }

            $document->doc_content = $docContent;
            $document->doc_format = strtolower($file->getClientOriginalExtension()) === 'pdf' ? 'pdf' : 'word';
        }

        // Mise à jour des autres champs
        $document->doc_name = $request->doc_name;
        $document->doc_type = $request->doc_type;
        $document->doc_updated_date = Carbon::now();
        $document->save();

        // Réindexation dans Elasticsearch
        $this->indexDocumentInElastic($document);

        return redirect()->route('documents.index')->with('success', 'Document mis à jour avec succès.');
    }

    // Suppression d'un document
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        // Supprimer le fichier du disque
        Storage::disk('public')->delete($document->doc_file_full_path);

        // Tente de supprimer l’index dans Elasticsearch
        try {
            $this->elasticClient->delete([
                'index' => 'documents',
                'id'    => $document->doc_id,
            ]);
        } catch (\Elastic\Elasticsearch\Exception\ClientResponseException $e) {
            // Si le document n'existe pas dans Elasticsearch, ignorer l'erreur 404
            if ($e->getCode() == 404) {
                Log::info("Le document {$document->doc_id} n'existe pas dans Elasticsearch.");
            } else {
                // Pour toute autre erreur, relancer l'exception ou gérer autrement
                throw $e;
            }
        }

        // Supprimer le document de MongoDB
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document supprimé.');
    }


    // Recherche full-text sur doc_content avec option de filtre sur doc_type

    public function search(Request $request)
    {
        $queryText = trim($request->input('query', ''));
        $docType = trim($request->input('doc_type', ''));

        try {
            // Construction de la requête de base
            $params = [
                'index' => 'documents',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [],
                            'filter' => []
                        ]
                    ],
                    'highlight' => [
                        'pre_tags' => ['<mark class="search-highlight">'],
                        'post_tags' => ['</mark>'],
                        'fields' => [
                            'doc_content' => new \stdClass()
                        ]
                    ],
                    'sort' => [
                        '_score' => ['order' => 'desc'],
                        'doc_insert_date' => ['order' => 'desc']
                    ]
                ]
            ];

            // Recherche textuelle
            if (!empty($queryText)) {
                $params['body']['query']['bool']['must'][] = [
                    'match' => [
                        'doc_content' => [
                            'query' => $queryText,
                            'analyzer' => 'french'
                        ]
                    ]
                ];
            } else {
                $params['body']['query']['bool']['must'][] = ['match_all' => new \stdClass()];
            }

            // Filtre par type
            if (!empty($docType)) {
                $params['body']['query']['bool']['filter'][] = [
                    'term' => ['doc_type' => $docType]
                ];
            }

            $response = $this->elasticClient->search($params);
            $results = $response->asArray();

            // Traitement des résultats avec gestion d'erreur pour mb_strimwidth
            $documents = [];
            foreach ($results['hits']['hits'] ?? [] as $hit) {
                $content = $hit['_source']['doc_content'] ?? '';
                $highlight = isset($hit['highlight']['doc_content'])
                    ? implode(' [...] ', $hit['highlight']['doc_content'])
                    : Str::limit($content, 200); // Utilisez Str::limit au lieu de mb_strimwidth

                $documents[] = [
                    'doc_id' => $hit['_source']['doc_id'],
                    'doc_name' => $hit['_source']['doc_name'],
                    'doc_type' => $hit['_source']['doc_type'],
                    'highlight' => $highlight,
                    'doc_file_full_path' => $hit['_source']['doc_file_full_path']
                ];
            }

            $docTypes = DocumentType::pluck('name', 'name')->toArray();
            $selectedType = $request->input('doc_type');

            return view('documents.search', compact('documents', 'docTypes', 'queryText', 'docType', 'selectedType'));
        } catch (ElasticsearchException $e) {
            Log::error("Erreur Elasticsearch : " . $e->getMessage());
            return back()->withErrors('La recherche a échoué : ' . $e->getMessage());
        }
    }
    /**
     * Indexe un document dans Elasticsearch
     * 
     * @param \App\Models\Document $document
     * @throws \Exception Si l'indexation échoue
     */
    public function indexDocumentInElastic(Document $document): void
    {
        try {
            // Validation des champs requis
            if (empty($document->doc_id)) {
                throw new \InvalidArgumentException("Le document doit avoir un doc_id");
            }

            // Préparation des données avec des valeurs par défaut pour les champs nullable
            $params = [
                'index' => 'documents',
                'id'    => $document->doc_id,
                'body'  => [
                    'doc_id'             => $document->doc_id,
                    'doc_name'           => $document->doc_name ?? '',
                    'doc_type'           => $document->doc_type ?? 'unknown',
                    'doc_content'        => $document->doc_content ?? '',
                    'doc_format'         => $document->doc_format ?? 'unknown',
                    'doc_insert_date'    => optional($document->doc_insert_date)->format('c') ?? now()->format('c'),
                    'doc_updated_date'   => optional($document->doc_updated_date)->format('c') ?? now()->format('c'),
                    'doc_file_full_path' => $document->doc_file_full_path ? Storage::path($document->doc_file_full_path) : '',
                ],
            ];

            $response = $this->elasticClient->index($params);

            if (!$response->asBool()) {
                throw new \RuntimeException("L'indexation a échoué sans erreur explicite");
            }
        } catch (ClientResponseException $e) {
            Log::error("Erreur Elasticsearch (Client) : " . $e->getMessage(), [
                'document_id' => $document->doc_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (ServerResponseException $e) {
            Log::error("Erreur Elasticsearch (Server) : " . $e->getMessage(), [
                'document_id' => $document->doc_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erreur inattendue lors de l'indexation : " . $e->getMessage(), [
                'document_id' => $document->doc_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Télécharge le fichier associé au document.
     *
     * @param  string  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $document = Document::findOrFail($id);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk->download($document->doc_file_full_path);
    }

    /**
     * Extrait le texte d'un fichier PDF ou Word
     * 
     * @param string $filePath Chemin complet du fichier
     * @param string $format Format du fichier ('pdf' ou 'word')
     * @return string|null Texte extrait ou null en cas d'échec
     */
    private function extractTextFromFile(string $filePath, string $format): ?string
    {
        try {
            if ($format === 'pdf') {
                return $this->extractTextFromPdf($filePath);
            } elseif ($format === 'word') {
                return $this->extractTextFromWord($filePath);
            }

            throw new \InvalidArgumentException("Format de fichier non supporté: $format");
        } catch (\Exception $e) {
            Log::error("Erreur d'extraction - Fichier: $filePath", [
                'format' => $format,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Extrait le texte d'un fichier PDF
     */
    private function extractTextFromPdf(string $filePath): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new \RuntimeException("La bibliothèque PDFParser n'est pas installée");
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);

        // Vérification de l'objet retourné
        if (!method_exists($pdf, 'getText')) {
            throw new \RuntimeException("La méthode getText() n'existe pas sur l'objet PDF");
        }

        $text = $pdf->getText();
        return $this->cleanExtractedText($text);
    }

    /**
     * Extrait le texte d'un fichier Word
     */
    private function extractTextFromWord(string $filePath): string
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            throw new \RuntimeException("La bibliothèque PHPWord n'est pas installée");
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        /** @var \PhpOffice\PhpWord\Element\Text $textElement */
                        if (method_exists($textElement, 'getText')) {
                            $text .= $textElement->getText();
                        }
                    }
                }
            }
        }

        return $this->cleanExtractedText($text);
    }


    /**
     * Nettoie le texte extrait
     */
    private function cleanExtractedText(string $text): string
    {
        // Suppression des espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        // Suppression des caractères non imprimables
        return trim(preg_replace('/[^\P{C}\n]+/u', '', $text));
    }
}
