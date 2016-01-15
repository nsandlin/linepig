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
$columns = array('irn', 'MulIdentifier', 'MulTitle', 'MulMimeType','<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)'); 
$results = $module->fetch('start', 0, -1, $columns);
$records = $results->rows;
$count = $results->count;
$display = "";
$sciname = "";
  
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

  // Build the filepath to image.
  $multimedia_url = "";
  $multimedia_url = '/' . substr($irn_string, -3, 3) . $multimedia_url;
  $irn_string = substr_replace($irn_string, '', -3, 3);
  $multimedia_url = "/" . $irn_string . $multimedia_url;
  $multimedia_url = 'http://cornelia.fieldmuseum.org' . $multimedia_url . '/' . $record['MulIdentifier'];
  
  // Convert to thumb.
  $multimedia_url = str_replace(".jpg",".thumb.jpg",$multimedia_url);
  
  // Build URL for detail page.
  $imgsrc = '<div class="item flex-item"><p>' . $thisspecies . '</p><a href="detail2.php?irn=' . $record['irn'] . '&amp;taxoirn=' .$taxo_irn . '"><img src="' . $multimedia_url . '" width="140" ></a></div>';
  
  // And add it to the display items.
  $display .= $imgsrc;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LinEpig - A resource for ID of female erigonines</title>
  <meta name="description" content="Microscopy images of <?php print $sciname ?> (family Linyphiidae)">
  <meta name="author" content="LinEpig, Field Museum of Natural History">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="http://fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="icon" type="image" href="images/favicon.ico">
  <link rel="stylesheet" type="text/css" href="css/style-basic.css" />
</head>
<body>
  <div class="container container-top">
  <h1><a href="/">LinEpig:</a> <i><?php print $sciname; ?></i></h1>
  <p>Displaying all available <?php print $flag; ?> images.</p>
  </div><!-- container -->
  
  <div class="container items flex-container">
  <!-- Start items -->
  
  <?php print $display; ?>
  
  <!-- End items -->
  </div><!-- container-->
  
  <div id="bottomnav">
    <a href="/index.php">LinEpig main page</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">Scientific American blog post</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a>
  </div><!--bottomnav-->
  
</body>
</html>

