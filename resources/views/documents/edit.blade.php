@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le Document</h1>
    
    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('documents.update', $document->doc_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Nom du document*</label>
                    <input type="text" name="doc_name" class="form-control" 
                           value="{{ old('doc_name', $document->doc_name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type de document*</label>
                    <select name="doc_type" class="form-select" required>
                        @foreach($docTypes as $type)
                            <option value="{{ $type }}" 
                                {{ $document->doc_type == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fichier actuel</label>
                    <div class="form-control bg-light">
                        {{ basename($document->doc_file_full_path) }}
                        <br>
                        @if(Storage::disk('public')->exists($document->doc_file_full_path))
                            <small class="text-muted">
                                Taille : {{ round(Storage::disk('public')->size($document->doc_file_full_path) / 1024, 2) }} Ko
                            </small>
                        @else
                            <small class="text-danger">Fichier introuvable</small>
                        @endif
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Nouveau fichier (laisser vide pour conserver)</label>
                        <input type="file" name="doc_file" class="form-control" 
                               accept=".pdf,.doc,.docx">
                        <small class="text-muted">Le contenu sera ré-extrait si vous changez de fichier</small>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection