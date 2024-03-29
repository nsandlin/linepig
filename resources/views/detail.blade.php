@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', $record['genus_species'])

@section('bodyclass')
<body class="detail-page">
@endsection

@section('content')
  <div class="flex-container blue">  
    <div class="flex-item">
          <h4 style="margin:30px 0 0 0;">{{ $record['MulTitle'] }}</h4>
          <img src="{{ $record['image_url'] }}" class="detail-pic">
          <p><i>{{ $record['genus_species'] }}</i> {{ $record['author'] }}</p>
          <p>
            @if (!empty($record['DetSource']))
              {{ $record['DetSource'] }}
            @endif
            &nbsp;
            @if (!empty($record['collection_record_url']))
              <a href="{{ $record['collection_record_url'] }}" target="_blank">View collection record</a>
            @endif
          </p>
    <p>Image {!! $record['rights'] !!}</p>
    </div><!--.flex-item blue-->
 
    <div class="flex-item species-links">
      <!-- Previous and Next links -->
        @if (!empty($prev_next))
          @if (!empty($prev_next['prev']))
            <a href="{{ $prev_next['prev'] }}" class="detail-nav-link prev">&lt;&lt; previous</a>
          @endif
          @if (!empty($prev_next['next']))
            <a href="{{ $prev_next['next'] }}" class="detail-nav-link next">&gt;&gt; next</a>
          @endif
        @endif
      <!-- END - Previous and Next links -->

          <h4 class="species-title">Show all <i>{{ $record['genus_species'] }}</i>:</h4>
            <ul class="subset-list-links">
              @foreach ($record['subsets'] as $key => $value)
                @if ($value == true)
                  <li class="subset-list-item">
                    <a href="/subset/{{ $key }}/{{ $record['taxonomy_irn'] }}" class="subset-link">
                      {{ $key }}
                    </a>
                  </li>
                @endif
              @endforeach
              <li class="subset-list-item">
                <a href="/subset/all/{{ $record['taxonomy_irn'] }}" class="subset-link">
                  all images
                </a>
              </li>
            </ul>

    @if (!empty($record['bold_url']))
      <p class="bold-systems">
        <a href="{{ $record['bold_url'] }}" target="_blank">BOLD systems taxon page</a>
      </p>
    @endif

    <p class="wsc">
      <a href="{{ $record['world_spider_catalog_url'] }}" target="_blank">World Spider Catalog lookup</a>
    </p>
     </div><!--.flex-item species-links-->
  </div><!--.flex-container blue-->


  <div class="additional-info">

      <!-- Link to old/bad previous multimedia -->
      @if (!empty($record['wrong_multimedia']))
        <div class="wrong-multimedia"><hr>
          <p>{!! $record['wrong_multimedia']['narrative'] !!}</p>
          <p>
            @if (!empty($record['wrong_multimedia']['thumbnail_url']))
              <img src="{{ $record['wrong_multimedia']['thumbnail_url'] }}"
                   class="wrong-multimedia-thumb"
                   alt="{{ $record['wrong_multimedia']['taxon_to_display'] }}"
                   title="{{ $record['wrong_multimedia']['taxon_to_display'] }}" >
            @endif
          </p>

          @if (!empty($record['wrong_multimedia']['taxon_to_display']))
            {{ $record['wrong_multimedia']['taxon_to_display'] }}
          @endif
        </div>
      @endif

    {{-- Notes section --}}
    @if (!empty($record['annotation']))
      <div class="notes">
        <h2>Notes</h2>
        {{ $record['annotation'] }}
      </div>
    @endif

    @if (!empty($record['guid']))
       <br clear="both"><p style="text-align:right;margin-right:20px;color:#888;">
       Catalog irn: {{ $record['catirn'] }}<br>
       OccurrenceID: {{ $record['guid'] }}</p> 
    @endif

  
  </div>
</div><!--item-picbox-->
@endsection
