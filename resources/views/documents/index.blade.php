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
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-success mb-2">
                                <i class="bi bi-file-earmark-pdf"></i> {{ strtoupper($document->doc_format) }}
                            </div>
                            <h5 class="card-title">{{ $document->doc_name }}</h5>
                            <p class="card-text small text-muted">
                                {{ substr($document->doc_content, 0, 100) }}{{ strlen($document->doc_content) > 100 ? '...' : '' }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('documents.download', $document->doc_id) }}" 
                                   class="btn btn-sm btn-outline-secondary" title="Télécharger">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="{{ route('documents.edit', $document->doc_id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('documents.destroy', $document->doc_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Confirmer la suppression ?')" title="Supprimer">
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
