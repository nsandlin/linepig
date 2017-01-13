@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search Results')

@section('content')
  @if (empty($searchResults))
    <p>No search results, sorry.</p>
  @endif

  <div class="search-results-count">
    <p><strong>{{ number_format($resultsCount) }}</strong> total records returned.</p>
    <p>You search on:
      <ul class="search-conditions-list">
        @if (!empty($searchConditions))
          @foreach ($searchConditions as $condition)
            <li class="search-condition">{{ $condition }}</li>
          @endforeach
        @endif
      </ul>
    </p>
  </div>

  <div class="container items flex-container subset blue">
    @foreach ($searchResults as $result)
      @if (!empty($result->thumbnailURL))
        <div class="item flex-item">
          <p class="species-item">{{ $result->genus }} {{ $result->species }}</p>
            <a href="/multimedia/{{ $result->irn }}" class="species-link">
              <img src="{{ $result->thumbnailURL }}" class="species-thumbnail">
            </a>
        </div>
      @endif
    @endforeach
  </div><!-- container-->

  <div class="pager"></div>
@endsection
