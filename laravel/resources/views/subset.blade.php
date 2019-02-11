@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', "Microscopy images of " . $genus_species . " (family Linyphiidae)")
@section('species_name', $genus_species)

@section('bodyclass')
<body class="subset-page">
@endsection

@section('content')
  <p>Displaying all available <em>{{ $type }}</em> images.</p>
  <div class="container items flex-container subset blue">
    

    @foreach ($records as $record)
      <div class="item flex-item">
        <p class="species-item">{{ $record['species_name'] }}</p>
        <a href="/multimedia/{{ $record['irn'] }}" class="species-link">
          <img src="{{ $record['thumbnail_url'] }}" class="species-thumbnail">
        </a>
      </div>
    @endforeach
  </div><!-- container-->
@endsection
