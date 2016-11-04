<?php
// INDEX.PHP - LinEpig main page

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

// Create a Session and selecting the module we want to query.
$session = new IMuSession(EMU_IP, EMU_PORT);
$module = new IMuModule('emultimedia', $session);

// Adding our search terms.
$terms = new IMuTerms();
$terms->add('MulMultimediaCreatorRef_tab', '177281');
$terms->add('DetSubject_tab', 'epigynum');
$terms->add('DetSubject_tab', 'primary');

// Fetching results.
$hits = $module->findTerms($terms);
$module->sort('MulIdentifier');
$columns = array('irn', 'MulIdentifier', 'MulTitle', 'MulMimeType'); 
$results = $module->fetch('start', 0, -1, $columns);
$records = $results->rows;
$count = $results->count;
$display = "";
$rowcount = 0;
$specieslist = "";

// Loop through each record and construct the Multimedia URL.
foreach ($records as $record) {
  $irn_string = (string) $record['irn'];
  if ($irn_string == "562211") {continue;}
  $thisspecies =  $record['MulTitle'];
  $thisspecies = substr_replace($thisspecies, '',-16);
  if (@strpos($specieslist,$thisspecies) !== false) {continue;}
  $specieslist =  $specieslist . $thisspecies . ",";
  
  // Build the filepath to image.
  $multimedia_url = "";
  $multimedia_url = '/' . substr($irn_string, -3, 3) . $multimedia_url;
  $irn_string = substr_replace($irn_string, '', -3, 3);
  $multimedia_url = "/" . $irn_string . $multimedia_url;
  $multimedia_url = 'http://cornelia.fieldmuseum.org' . $multimedia_url . '/' . $record['MulIdentifier'];
    
  // Convert to thumb.
  $multimedia_url = str_replace(".jpg",".thumb.jpg",$multimedia_url);
  $multimedia_url = str_replace(".png",".thumb.jpg",$multimedia_url);
  
  // Build URL for detail page.
  $imgsrc = '<div class="item flex-item"><p class="species-item">' . $thisspecies . '</p><a href="detail.php?irn=' . $record['irn'] . '" class="species-link"><img src="' . $multimedia_url . '" class="species-thumbnail" ></a></div>';
  
  // And add it to the display.
  $display .= $imgsrc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LinEpig - A resource for ID of female erigonines</title>
  <meta name="description" content="A visual aid for identifying the difficult spiders in family Linyphiidae.">
  <meta name="author" content="LinEpig, Field Museum of Natural History">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="http://fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="icon" type="image" href="images/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans|Noto+Serif" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="css/style-basic.css" />
</head>
<body class="home">
  <div class="container container-top">
    <p style="float:right;"><img src="images/logo-field.png"></p><br clear="both">
    <h1>Welcome to LinEpig</h1>
    <p>Get help identifying the erigonines languishing in your collection.
    <br>We have epigynal images for <b><?php print $count; ?> species</b> of Erigoninae so far. Read more <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">about LinEpig</a> and <a href="https://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">help us grow</a>.</p>
  </div><!--.container container-top-->
  
  <div class="container items flex-container all-epig blue">
  <!-- Start items -->
  
  <?php print $display; ?>
  
  <!-- End items -->
  </div><!-- container-->
  
  <div id="bottomnav">
    <a href="/index.php">LinEpig main page</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery" target="_blank">About</a> - 
    <a href="http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/" target="_blank">At SciAm</a> - 
    <a href="http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help" target="_blank">Contribute specimens/images</a> - 
    <a href="https://github.com/nsandlin/linepig" target="_blank">GitHub</a>
  </div><!--bottomnav-->
  
</body>
</html>

