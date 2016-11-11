@extends('master')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('count', $count)

@section('content')
  <div class="container items flex-container all-epig blue">
    <!-- Start items -->
    <ul class="records-list">
      @foreach ($records as $record)
        <li class="record">
        </li>
      @endforeach
    </ul>
    <!-- End items -->
  </div><!-- container-->
@endsection
