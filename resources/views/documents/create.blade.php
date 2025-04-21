@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nouveau Document</h1>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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
                    <input type="text" name="doc_name" class="form-control" value="{{ old('doc_name') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Type de document*</label>
                    <select name="doc_type" class="form-select">
                        <option value="" disabled {{ old('doc_type') ? '' : 'selected' }}>Choisir un type...</option>
                        @foreach($docTypes as $type)
                            <option value="{{ $type }}" {{ old('doc_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fichier* (PDF ou Word)</label>
                    <input type="file" name="doc_file" class="form-control" accept=".pdf,.doc,.docx">
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
