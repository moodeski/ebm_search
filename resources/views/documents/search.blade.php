@extends('layouts.app')

@section('content')
<div class="container">
    <h1>
        Résultats de recherche
        @if(!empty($queryText) && !empty($selectedType))
            pour "{{ $queryText }}" dans le type "{{ $selectedType }}"
        @elseif(!empty($queryText))
            pour "{{ $queryText }}"
        @elseif(!empty($selectedType))
            pour le type "{{ $selectedType }}"
        @endif
    </h1>

    <div class="card">
        <div class="card-body">
            @if(count($documents) > 0)
                <div class="list-group">
                    @foreach($documents as $doc)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>{{ $doc['doc_name'] }}</h5>
                                <div class="text-muted mb-2">
                                    <span class="badge bg-primary">{{ $doc['doc_type'] }}</span>
                                </div>
                                @if($doc['highlight'])
                                    <div class="search-highlight">
                                        {!! $doc['highlight'] !!}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('documents.download', $doc['doc_id']) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    Aucun résultat trouvé pour votre recherche.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<style>
.search-highlight em {
    background-color: #fff3cd;
    font-style: normal;
    font-weight: bold;
}
</style>