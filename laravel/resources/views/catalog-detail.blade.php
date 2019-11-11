@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', $record['genus_species'])

@section('content')
  <div class="flex-container blue" style="padding:0 5px 19px 5px;">  
    <div class="flex-item">

      {{-- Catalogue Images container --}}
        @if (!empty($record['multimedia']))
          <div class="multimedia-container">
            @foreach ($record['multimedia'] as $multimedia)
              <div class="catalogue-multimedia multimedia">
                <a href="/multimedia/{{ $multimedia['irn'] }}" class="catalogue-multimedia-link">
                  <img src="{{ $multimedia['thumbnail_url'] }}"
                       alt="{{ $multimedia['MulDescription'] }}"
                       title="{{ $multimedia['MulTitle'] }}">
                </a>
              </div>
            @endforeach
          </div>
        @endif

      <div class="collection-data">Collection data: {{ $record['collection_data'] }}</div>

      <div class="total-count">Total count: {{ $record['total_count'] }}</div>

      @if (!empty($record['semaphoronts']))
      <div class="semaphoronts">
        <ul class="semaphoronts-list">
          @foreach($record['semaphoronts'] as $key => $value)
            <li class="semaphoront">
              {{ $key }}: {{ $value }}
            </li>
          @endforeach
        </ul>
      </div>
      @endif

      <div class="identified-by">Identified by: {{ $record['identified_by'] }}</div>

      <div class="date-identified">Date identified: {{ $record['date_identified'] }}</div>

      <div class="collection-event">Collection Event/locality: {{ $record['collection_event'] }}</div>

      <div class="collection-method">Collection Method: {{ $record['collection_method'] }}</div>

      <div class="collection-event-code">Collection Event Code: {{ $record['collection_event_code'] }}</div>

      <div class="date-visited-from">Date Visited From: {{ $record['date_visited_from'] }}</div>

      <div class="date-visited-to">Date Visited To: {{ $record['date_visited_to'] }}</div>

      <div class="collected-by">Collected by: {{ $record['collected_by'] }}</div>

      <div class="lat-lng">Lat/Lng: {{ $record['lat'] }} {{ $record['lng'] }}</div>

      <div class="elevation">Elevation: {{ $record['elevation'] }} feet</div>

      <div class="habitat">Habitat: {{ $record['habitat'] }}</div>
    </div><!--.flex-item blue-->
  </div><!--.flex-container blue-->


    @if (!empty($record['guid']))
      <div class="notes" style="background:#ccc;border-bottom:solid 2px #069; border-radius:10px;">
       <br clear="both"><p style="text-align:right;margin-right:20px;color:#888;">OccurrenceID: {{ $record['guid'] }}</p>
      </div>
    @endif
</div><!--item-picbox-->
@endsection
