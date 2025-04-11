@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer un nouveau type</h1>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('document_types.store') }}" method="POST">
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
                    <label for="name" class="form-label">Nom du type *</label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           required
                           value="{{ old('name') }}"
                           placeholder="Ex: CV, Fiche de poste...">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('document_types.index') }}" 
                       class="btn btn-outline-secondary">
                       <i class="bi bi-arrow-left me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection