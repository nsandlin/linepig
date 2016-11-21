@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search Results')

@section('content')
  @if (empty($searchResults))
    <p>No search results, sorry.</p>
  @endif

  <div class="search-results-count">
    <strong>{{ number_format($resultsCount) }}</strong> total records returned.
  </div>

  <div class="container items flex-container subset blue">
    @foreach ($searchResults as $result)
      <div class="item flex-item">
        <p class="species-item">{{ $result->genus }} {{ $result->species }}</p>
        <a href="/multimedia/{{ $result->irn }}" class="species-link">
          <img src="{{ $result->thumbnailURL }}" class="species-thumbnail">
        </a>
      </div>
    @endforeach
  </div><!-- container-->

  <div class="pager"></div>
@endsection
