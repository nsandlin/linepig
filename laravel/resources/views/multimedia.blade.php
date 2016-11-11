@extends('master-multimedia')
@section('title', 'LinEpig - A resource for ID of female erigonines')
@section('description', 'A visual aid for identifying the difficult spiders in family Linyphiidae.')
@section('species_name', $record['MulTitle'])

@section('content')
  <div class="flex-container blue">  
    <div class="flex-item">
          <h4 style="margin:30px 0 0 0;">Female epigynum, ventral view.</h4>
          <img src="{multimedia_url}" class="detail-pic"><p><i>{thisspecies}</i> {authorstring}</p>
    </div><!--.flex-item blue-->
 
    <div class="flex-item species-links">
          <h4 class="species-title">Show all <i>{thisspecies}</i>:</h4>
            <ul class="subset-list-links">{subset_list_items}</ul>
     </div><!--.flex-item species-links-->
  </div><!--.flex-container blue-->


  <div class="additional-info">
    <p><span class="label">Taxonomy</span></p><!--adds-->

    <p><br><span class="label">Material</span></p><p>{thiscredit}&nbsp;<!--collrecd--></p>
      <p>Image {rrights}</p>

      <!-- Link to old/bad previous image -->
      <div class="backlink-old-image" style="display:none;"><hr>
        <p><b>Note:</b> This species was previously incorrectly represented here as<br>
          <a href="{backlinkimage}" target="_blank"><img src="{backlinkimage}" width="160px"><br>Click to enlarge<a>
        </p>
      </div>
  </div>
</div><!--item-picbox-->
@endsection
