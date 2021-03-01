@extends('layout-for-individual-pages')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', 'Search')

@section('content')
  <div class="flex-container">  
    <div class="flex-item">
      <div class="search-container">
        <div id="search-form">
          <form method="POST" action="/search-handle">
            @csrf
            <fieldset class="search-fieldset">
              <label for="genus">Genus</label>
              <input name="genus" type="text" id="genus">
            </fieldset>

            <fieldset class="search-fieldset">
              <label for="species">Species</label>
              <input name="species" type="text" id="species">
            </fieldset>

            <fieldset class="search-fieldset keywords">
              <label for="keywords">Keywords</label>

              <ul class="keywords-option-list">
                @foreach ($keywords as $keyword)
                  <li class="keyword-option-item">
                    <label for="{{ $keyword }}">{{ $keyword }}</label>
                    <input name="keywords[]" type="checkbox" value="{{ $keyword }}">
                  </li>
                @endforeach
              </ul>
            </fieldset>

            <input class="search" type="submit" value="Search">
          </form>
        </div>
      </div>
    </div><!--.flex-item blue-->

  </div><!--.flex-container blue-->

</div><!--item-picbox-->
@endsection
