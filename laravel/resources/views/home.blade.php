@extends('header')

@section('title', $title)

@section('description', $description)

@section('content')
  <div class="container items flex-container all-epig blue">
    <!-- Start items -->
    <ul class="records-list">
      @foreach ($records as $record)
        <li class="record">
          {{ dd($record) }}
        </li>
      @endforeach
    </ul>
    <!-- End items -->
  </div><!-- container-->
@endsection

@extends('footer')
