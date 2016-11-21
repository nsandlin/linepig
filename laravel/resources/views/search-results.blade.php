@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search Results')

@section('content')
  <div class="flex-container">  
    <div class="flex-item">
      <div class="search-container">
        <div class="search-results-container">
          <p><a href="/search">&lt;&lt; search again</a></p>

          @if (empty($searchResults))
            <p>No search results, sorry.</p>
          @endif

          <div class="search-results-count">
            <strong>{{ number_format($resultsCount) }}</strong> total records returned.
          </div>

          <?php // Initialize counter
              $i = 1;
          ?>
          @foreach ($searchResults as $result)
            <?php
                if ($i % 2 == 0) {
                    $row_class = " even";
                }
                else {
                    $row_class = " odd";
                }
            ?>
            <div class="result-container{{ $row_class }}">
              <div class="thumbnail">IRN: {{ $result->irn }}</div>
              <div class="search">Search field: {{ $result->search }}</div>
            </div>
            <?php
                $i++;
            ?>
          @endforeach

           {!! $searchResults->links() !!}
        </div>
      </div>
    </div><!--.flex-item blue-->

  </div><!--.flex-container blue-->

</div><!--item-picbox-->
@endsection
