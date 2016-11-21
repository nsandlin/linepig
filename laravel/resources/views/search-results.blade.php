@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search Results')

@section('content')
  <div class="flex-container">  
    <div class="flex-item">
      <div class="search-container">
        <h1>Search Results</h1>

        <div class="search-results-container">
          @if (empty($searchResults))
            <p>No search results, sorry.</p>
          @endif

          <div class="search-results-count">
            <strong>{{ number_format($resultsCount) }}</strong> total records returned.
          </div>

          @foreach ($searchResults as $result)
            <div class="result-container">
              <div class="irn">IRN: {{ $result->irn }}</div>
              <div class="module">Module: {{ $result->module }}</div>
              <div class="search">Search field: {{ $result->search }}</div>
            </div>
          @endforeach

           {!! $searchResults->links() !!}
        </div>
      </div>
    </div><!--.flex-item blue-->

  </div><!--.flex-container blue-->

</div><!--item-picbox-->
@endsection
