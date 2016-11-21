@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search')

@section('content')
  <div class="flex-container">  
    <div class="flex-item">
      <div class="search-container">
        <h1>Search for records</h1>
        <div id="search-form">
          {!! Form::open(['action' => 'SearchController@handleSearch']) !!}
          {!! Form::token() !!}
          {!! Form::text('searchTerms') !!}
          {!! Form::submit('Search') !!}
          {!! Form::close() !!}
        </div>
      </div>
    </div><!--.flex-item blue-->

  </div><!--.flex-container blue-->

</div><!--item-picbox-->
@endsection
