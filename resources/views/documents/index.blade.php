@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Gestion des Documents</h1>

    <!-- Barre de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('documents.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="query" class="form-control" placeholder="Rechercher dans le contenu..." value="{{ old('query') }}">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bouton d'ajout -->
    <div class="mb-3">
        <a href="{{ route('documents.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nouveau Document
        </a>
    </div>

    <!-- Tableau des documents -->
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Format</th>
                        <th>Date d'ajout</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                    <tr>
                        <td>{{ $document->doc_name }}</td>
                        <td>{{ $document->doc_type }}</td>
                        <td>{{ strtoupper($document->doc_format) }}</td>
                        <td>{{ $document->doc_insert_date->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('documents.edit', $document->doc_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('documents.download', $document->doc_id) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="{{ route('documents.destroy', $document->doc_id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection