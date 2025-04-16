@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Documents disponibles</h1>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajouter un document
        </a>
    </div>

    <!-- Barre de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('documents.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="query" class="form-control" placeholder="Rechercher..." value="{{ old('query') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="doc_type" class="form-select">
                            <option value="">Tous les types</option>
                            @foreach($docTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Affichage en cartes par type -->
    @foreach($docTypes as $type)
        @php $filteredDocs = $documents->where('doc_type', $type); @endphp
        @if($filteredDocs->isNotEmpty())
        <div class="mb-4">
            <h4 class="mb-3">{{ $type }}</h4>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach($filteredDocs as $document)
                @php
                    $icon = match(strtolower($document->doc_format)) {
                    'pdf' => ['icon' => 'bi-file-earmark-pdf', 'color' => 'text-danger'], 
                    'word' => ['icon' => 'bi-file-earmark-word', 'color' => 'text-primary'], 
                   };
                @endphp
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2 fs-5 {{ $icon['color'] }}">
                                <i class="bi {{ $icon['icon'] }}"></i> 
                                <span class="fw-bold">{{ strtoupper($document->doc_format) }}</span>
                            </div>
                            <h5 class="card-title">{{ $document->doc_name }}</h5>
                            <p class="card-text small text-muted mb-3">
                                {{ Str::limit($document->doc_content, 100) }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="small text-muted mb-2">
                                <div><i class="bi bi-calendar-plus"></i> Créé le: {{ $document->created_at->format('d/m/Y H:i') }}</div>
                                <div><i class="bi bi-calendar-check"></i> Modifié le: {{ $document->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="justify-content-end gap-2 align-items-center d-flex">
                                <a href="{{ route('documents.download', $document->doc_id) }}" 
                                   class="btn btn-sm btn-outline-secondary" title="Télécharger">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="{{ route('documents.edit', $document->doc_id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('documents.destroy', $document->doc_id) }}" method="POST" class="d-flex m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Supprimer" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach

    @if($documents->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-folder2-open display-1 text-muted"></i>
        <p class="mt-3 text-muted">Aucun document disponible</p>
    </div>
    @endif
</div>
@endsection
<style>
    .card {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        border: 1px solid #e0e0e0;
        background-color: #ffffff;
        margin-bottom: 1.5rem;
        padding: 1rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        text-align: left;
        color: #212529;
        font-size: 1rem;
        line-height: 1.5;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .card-body {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        text-align: left;
        color: #212529;
        font-size: 1rem;
        line-height: 1.5;
        transition: background-color 0.2s;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 500;
        color:rgb(0, 0, 0);
        margin-bottom: 0.5rem;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.2s;
    }
    
    .card-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 1rem;
        text-align: left;
        line-height: 1.5;
        transition: color 0.2s;
    }
    .card-footer {
        background-color: #ffffff;
        border-top: 1px solid #e0e0e0;
        padding: 1rem;
        text-align: left;
        color:rgb(41, 33, 33);
        font-size: 0.875rem;
        line-height: 1.5;
        transition: background-color 0.2s;
    }

    .text-muted {
        font-size: 0.9em;
        color: #6c757d;
        margin-bottom: 0.5rem;
        text-align: center;
        transition: color 0.2s;
    }

    /* Couleurs spécifiques pour les types de fichiers */
    .bi-file-earmark-pdf { color: #dc3545 !important; } /* Rouge PDF */
   .bi-file-earmark-word { color:hsl(223, 92.50%, 47.30%) !important; } /* Bleu Word Office */

  /* Style cohérent pour le format */
  .bi-file-earmark-pdf,
  .bi-file-earmark-word {
    font-size: 1.5em; /* Taille légèrement plus grande pour l'icône */
    vertical-align: middle; /* Alignement vertical parfait */
}
    
</style>

<!--
    - Le code ci-dessus est un exemple de la vue index pour afficher les documents disponibles.
    - Il utilise Bootstrap pour le style et la mise en page.
    - La barre de recherche permet de filtrer les documents par nom ou type.
    - Les documents sont affichés sous forme de cartes, regroupés par type.
    - Chaque carte affiche le nom, le format, la date de création et de modification du document.
    - Des boutons permettent de télécharger, modifier ou supprimer chaque document.
    - Un message est affiché si aucun document n'est disponible.
    - Le style CSS ajoute un effet d'ombre et de translation lors du survol des cartes.
    - Les icônes Bootstrap sont utilisées pour améliorer l'interface utilisateur.
    - Le code est organisé pour être facilement lisible et maintenable. -->
