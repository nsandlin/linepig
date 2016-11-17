@extends('layout-home')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('count', $count)

@section('content')
  <div class="container items flex-container all-epig blue">
    <!-- Start items -->
      @foreach ($records as $record)
        <div class="item flex-item">
          <p class="species-item">{{ $record['species_name'] }}</p>
          <a href="/multimedia/{{ $record['irn'] }}" class="species-link">
            <img src="{{ $record['thumbnail_url'] }}"
                 class="species-thumbnail"
                 alt="{{ $record['MulTitle'] }}"
                 title="{{ $record['MulTitle'] }}" />
          </a>
        </div>
      @endforeach
    </div>
    <!-- End items -->
  </div><!-- container-->
@endsection
