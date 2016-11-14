@extends('master-multimedia')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', "Microscopy images of " . $genus_species . " (family Linyphiidae)")
@section('species_name', $genus_species)

@section('content')
  <div class="container items flex-container blue">
    <p>Displaying all available {{ $type }} images.</p>

    @foreach ($records as $record)
      <div class="item flex-item">
        <p>{{ $record['species_name'] }}</p>
        <a href="/multimedia/{{ $record['irn'] }}">
          <img src="{{ $record['thumbnail_url'] }}" width="140">
        </a>
      </div>
    @endforeach
  </div><!-- container-->
@endsection
