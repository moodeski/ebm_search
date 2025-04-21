<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour gérer les types de documents
 * - CRUD basique (index, create, store, edit, update, destroy)
 * - Indexation des types dans Elasticsearch
 */
class DocumentTypeController extends Controller
{
    /**
     * Instance du client Elasticsearch
     * @var \Elastic\Elasticsearch\Client
     */
    protected $elasticClient;

    /**
     * Affiche la liste de tous les types de documents
     * Route : GET /document_types
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupère tous les enregistrements du modèle DocumentType
        $documentTypes = DocumentType::all();

        // Passe la collection à la vue resources/views/document_types/index.blade.php
        return view('document_types.index', compact('documentTypes'));
    }

    /**
     * Montre le formulaire de création d'un nouveau type de document
     * Route : GET /document_types/create
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('document_types.create');
    }

    /**
     * Enregistre un nouveau type de document en base et dans Elasticsearch
     * Route : POST /document_types
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validation des données entrantes
        $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name',
        ]);

        // Création en base de données
        $documentType = DocumentType::create([
            'name' => $request->name,
        ]);

        // Indexation du nouveau type dans Elasticsearch
        $this->indexDocumentTypeInElastic($documentType);

        // Redirection vers la liste avec un message de succès
        return redirect()
            ->route('document_types.index')
            ->with('success', 'Type de document créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition pour un type existant
     * Route : GET /document_types/{id}/edit
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Récupère ou échoue si non trouvé
        $documentType = DocumentType::findOrFail($id);

        // Passe l'entité à la vue resources/views/document_types/edit.blade.php
        return view('document_types.edit', compact('documentType'));
    }

    /**
     * Met à jour un type de document existant
     * Route : PUT/PATCH /document_types/{id}
     * @param  Request  $request
     * @param  int      $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Récupère l'entité
        $documentType = DocumentType::findOrFail($id);

        // Validation (ignore l'ID courant pour la règle unique)
        $request->validate([
            'name' => 'required|string|max:100|unique:document_types,name,' . $documentType->_id,
        ]);

        // Mise à jour du nom
        $documentType->update([
            'name' => $request->name,
        ]);

        // Redirection avec message
        return redirect()
            ->route('document_types.index')
            ->with('success', 'Type de document mis à jour.');
    }

    /**
     * Supprime un type de document
     * Route : DELETE /document_types/{id}
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Récupère ou échoue si non trouvé
        $documentType = DocumentType::findOrFail($id);

        // Suppression en base
        $documentType->delete();

        // Redirection avec message
        return redirect()
            ->route('document_types.index')
            ->with('success', 'Type de document supprimé.');
    }

    /**
     * Indexe un type de document dans Elasticsearch
     * @param  DocumentType  $documentType
     * @return void
     */
    public function indexDocumentTypeInElastic(DocumentType $documentType)
    {
        // Récupère la configuration Elasticsearch (premier hôte)
        $config = config('elasticsearch.hosts')[0];

        // Construit la chaîne de connexion selon présence d'auth
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

        // Initialise le client Elasticsearch
        $this->elasticClient = ClientBuilder::create()
            ->setHosts([$hostString])
            ->setRetries($config['retries'])
            ->build();

        // Vérifie si l'index existe, sinon le crée
        try {
            $this->elasticClient->indices()->get(['index' => 'document_types']);
        } catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $e) {
            if ($e->getCode() === 404) {
                // Utilise l'analyzer défini ou 'standard'
                $analyzer = $config['analyzer'] ?? 'standard';
                $this->createDocumentTypeIndex($analyzer);
            } else {
                // Log d'erreur si autre exception
                Log::error("Erreur Elasticsearch dans indexDocumentTypeInElastic: " . $e->getMessage());
            }
        }

        // Prépare les données pour l'indexation
        $params = [
            'index' => 'document_types',
            'id'    => $documentType->_id,
            'body'  => [
                'name' => $documentType->name,
            ],
        ];

        // Tente d'indexer le document
        try {
            $this->elasticClient->index($params);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'indexation du type de document : " . $e->getMessage());
        }
    }

    /**
     * Crée l'index Elasticsearch pour les types de documents
     * @param  string  $analyzer
     * @return void
     */
    private function createDocumentTypeIndex(string $analyzer = 'standard'): void
    {
        $params = [
            'index' => 'document_types',
            'body'  => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'default' => [
                                'type' => $analyzer,
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        // Mappage du champ name en tant que mot-clé (keyword)
                        'name' => ['type' => 'keyword'],
                    ],
                ],
            ],
        ];

        // Création de l'index
        $this->elasticClient->indices()->create($params);
    }
}
