@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', $record['genus_species'])

@section('content')
  <div class="flex-container blue">  
    <div class="flex-item">
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

  </div>
</div><!--item-picbox-->
@endsection
