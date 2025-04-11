@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nouveau Document</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nom du document*</label>
                    <input type="text" name="doc_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type de document*</label>
                    <select name="doc_type" class="form-select" required>
                        @foreach($docTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fichier* (PDF ou Word)</label>
                    <input type="file" name="doc_file" class="form-control" accept=".pdf,.doc,.docx" required>
                    <small class="text-muted">Le contenu texte sera extrait automatiquement</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection