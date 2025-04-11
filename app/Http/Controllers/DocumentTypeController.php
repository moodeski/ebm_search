<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class DocumentTypeController extends Controller
{
    protected $elasticClient;
    // Afficher la liste des types de documents
    public function index()
    {
        $documentTypes = DocumentType::all();
        return view('document_types.index', compact('documentTypes'));
    }

    // Afficher le formulaire de création d’un nouveau type
    public function create()
    {
        return view('document_types.create');
    }

    // Stocker le nouveau type de document
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name',
        ]);

        $documentType = DocumentType::create(['name' => $request->name]);

        $this->indexDocumentTypeInElastic($documentType);

        return redirect()->route('document_types.index')->with('success', 'Type de document créé avec succès.');
    }

    // Afficher le formulaire d’édition d’un type de document
    public function edit($id)
    {
        $documentType = DocumentType::findOrFail($id);
        return view('document_types.edit', compact('documentType'));
    }

    // Mettre à jour le type de document
    public function update(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name,' . $documentType->_id,
        ]);

        $documentType->update(['name' => $request->name]);

        return redirect()->route('document_types.index')->with('success', 'Type de document mis à jour.');
    }

    // Supprimer un type de document
    public function destroy($id)
    {
        $documentType = DocumentType::findOrFail($id);
        $documentType->delete();

        return redirect()->route('document_types.index')->with('success', 'Type de document supprimé.');
    }

    public function indexDocumentTypeInElastic(DocumentType $documentType)
    {
        // Récupère la première configuration
        $config = config('elasticsearch.hosts')[0];

        // Construit une URL pour le host de la même manière que dans DocumentController
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

        // Vérifie si l'index 'document_types' existe ; s'il n'existe pas, le créer
        try {
            $this->elasticClient->indices()->get(['index' => 'document_types']);
        } catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $e) {
            if ($e->getCode() === 404) {
                // Utilisation d'un analyzer défini en configuration ou 'standard' par défaut
                $analyzer = $config['analyzer'] ?? 'standard';
                $this->createDocumentTypeIndex($analyzer);
            } else {
                Log::error("Erreur Elasticsearch dans indexDocumentTypeInElastic: " . $e->getMessage());
            }
        }

        // Prépare les paramètres d'indexation pour le type de document
        $params = [
            'index' => 'document_types',
            'id'    => $documentType->_id, // Vous pouvez adapter le champ d'identifiant si nécessaire
            'body'  => [
                'name' => $documentType->name,
            ],
        ];

        try {
            $this->elasticClient->index($params);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'indexation du type de document : " . $e->getMessage());
        }
    }

    private function createDocumentTypeIndex(string $analyzer = 'standard'): void
    {
        $params = [
            'index' => 'document_types',
            'body'  => [
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
                        'name' => ['type' => 'keyword']
                    ]
                ]
            ]
        ];

        $this->elasticClient->indices()->create($params);
    }
}
