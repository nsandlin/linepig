<?php
// SUBSET.PHP - displays a selected set of thumbnails
// called from detail.php

// Enable all error reporting.
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Setting up requirements.
require_once __DIR__.'/../imu-api-php/IMu.php';
require_once __DIR__.'/../imu-api-php/Session.php';
require_once __DIR__.'/../imu-api-php/Module.php';
require_once __DIR__.'/../imu-api-php/Terms.php';
require_once __DIR__.'/../.env';

// Get query string.
$taxo_irn = filter_var($_GET['irn'], FILTER_VALIDATE_INT);
$flag = filter_var($_GET['flag'], FILTER_SANITIZE_STRING);

/// Create a Session and select the module we want to query.
$session = new IMuSession(EMU_IP, EMU_PORT);
$module = new IMuModule('emultimedia', $session);

// Adding our search terms.
// Note
$terms = new IMuTerms();
$terms->add('MulOtherNumber_tab', $taxo_irn);
$terms->add('DetSubject_tab', $flag);

// Fetching results.: unlike Index.php, this list not exclude non-primary images
$hits = $module->findTerms($terms);
$columns = array('irn', 'MulIdentifier', 'MulTitle', 'MulMimeType','<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, irn)'); 
$results = $module->fetch('start', 0, -1, $columns);
$records = $results->rows;
$count = $results->count;
$display = "";
$colcount = 0;
$rowcount = 0;
$sciname = "";
  
$startrow = '<div class="row">';
$startcol = '<div class="one-half column"><table><tbody><tr>';
$endcol   = '</tr></tbody></table></div>';
$endrow   = '</div><!-- row -->';

// Loop through each record and construct the Multimedia URL.
foreach ($records as $record) {
  $this_mimetype = $record['MulMimeType'];
  if ($this_mimetype == "x-url") {continue;}
  $irn_string = (string) $record['irn'];
  //if ($irn_string == "562211") {continue;}
  $thisspecies =  $record['MulTitle'];
  $irn_length = strlen($irn_string);

  // Ensure the attached record is not empty.
  if (!empty($record['etaxonomy:MulMultiMediaRef_tab'])) {
    foreach ($record['etaxonomy:MulMultiMediaRef_tab'] as $mul_record) {
      $genus = $mul_record['ClaGenus'];
      $species = $mul_record['ClaSpecies'];
      $sciname = $genus . " " . $species;
    }
  }

  $multimedia_url = "";
  $multimedia_url = '/' . substr($irn_string, -3, 3) . $multimedia_url;
  $irn_string = substr_replace($irn_string, '', -3, 3);
  $multimedia_url = "/" . $irn_string . $multimedia_url;
  $multimedia_url = 'http://cornelia.fieldmuseum.org' . $multimedia_url . '/' . $record['MulIdentifier'];
  
  $imgsrc = '<td class="item"><a href="detail2.php?irn=' . $record['irn'] . '&amp;taxoirn=' .$taxo_irn . '"><img src="' . $multimedia_url . '" width="140" ></a><br>' . $thisspecies . '</td>';
  
  $rowcount++;
  if ( $rowcount == 1 ) {
  $display .= $startrow;
  }
  $colcount++;
  if ( $colcount == 1 ) {
  $display .= $startcol;
  }
  
  $display .= $imgsrc;

  if ( $colcount == 3 ) {
  $display .= $endcol;
  $colcount = 0;
  }
  if ( $rowcount == 6 ) {
  $display .= $endrow;
  $rowcount = 0;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page needs -->
  <meta charset="utf-8">
  <title>LinEpig - A resource for ID of female erigonines</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- Mobile-specific metas, font, css, & favicon -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="icon" type="image" href="images/favicon.ico">
  <style type="text/css">
/* Grid */
.container {
  position: relative;
  width: 100%;
  max-width: 960px;
  margin: 0 auto;
  box-sizing: border-box; }
/*  */
  html {
  font-size: 62.5%; }
body {
  font-size: 1.5em; /* currently ems cause chrome bug misinterpreting rems on body element */
  line-height: 1.6;
  font-weight: 400;
  font-family: "Raleway", "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif;
  color: #222; }
/* Typography */
h1, h2, h3, h4, h5, h6 {
  margin-top: 0;
  margin-bottom: 2rem;
  font-weight: 300; }
  
  div.items {padding: 35px;}
  div.items, table, tr, td {
  background: #E0EBEB;
  }
  td {
  font-family: Arial;
  font-size: 80%;
  color: #777;
  max-width: 142px;
  }
  div.one-half {
  float:left;
  }
  .items .row {
  width: 100%;
  }
  .items .row:after {
  content:'';
  display:block;
  clear:both;
  }
  </style>
</head>
<body>
  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
  <div class="row top">
  <div class="eleven columns" style="margin-top: 15%">
  <h1>LinEpig: <i><?php print $sciname; ?></i></h1>
  <p>Displaying all available <?php print $flag; ?> images.</p>
  </div><!-- 11 cols -->
  <div class="one column">
  </div><!-- 1 col -->
  </div><!-- row top -->
  </div><!-- container -->
  
  <div class="container items">
  <!-- Start items -->
  
  <?php print $display; ?>
  <!-- End items -->
  
  </div><!-- container -->
<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>

