@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Types de Documents</h1>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nom du type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentTypes as $type)
                            <tr>
                                <td>{{ $type->name }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('document_types.edit', $type->id) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <form action="{{ route('document_types.destroy', $type->id) }}" 
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Supprimer ce type ?')"
                                                    title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Aucun type enregistré</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('document_types.create') }}" 
       class="btn btn-success">
       <i class="bi bi-plus-circle me-2"></i>Ajouter un type
    </a>
</div>
@endsection