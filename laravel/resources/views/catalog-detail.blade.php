@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', $record['genus_species'])

@section('content')
  <div class="flex-container blue">  
    <div class="flex-item">
      <h1><i>{{ $record['genus_species'] }}</i></h1>
      <div class="catalog-number">{{ $record['catalog_number'] }}</div>
    </div><!--.flex-item blue-->
  </div><!--.flex-container blue-->

  </div>
</div><!--item-picbox-->
@endsection
