<?php
// DETAIL2.PHP - displays large image & image/specimen details
// bascially the same as detail.php but called from subset.php

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
$irn = filter_var($_GET['irn'], FILTER_VALIDATE_INT);
$taxo_irn = filter_var($_GET['taxoirn'], FILTER_VALIDATE_INT);

// Create a Session and selecting the module we want to query.
$session = new IMuSession(EMU_IP, EMU_PORT);
$module = new IMuModule('emultimedia', $session);

// Adding our search terms.
$terms = new IMuTerms();
$terms->add('irn', $irn);

// Fetching results.
$hits = $module->findTerms($terms);
$columns = array(
            'irn', 'MulIdentifier', 'MulTitle',
            'DetSource', 'MulOtherNumber_tab', 'DetMediaRightsRef.(SummaryData)',
            '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus,ClaSpecies,AutAuthorString)',
            'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)', // Added related media to construct collection link.
);
$results = $module->fetch('start', 0, 1, $columns);
$record = $results->rows[0];
$imgtitle = $record['MulTitle'];
$irn_string = $irn;
$irn_length = strlen($irn_string);
$num_of_divisions = $irn_length / 3;
$taxo_irn = $record['MulOtherNumber_tab'][0]; // Are we sure we only have one item in the Other Number field?
$thiscredit = $record['DetSource'];
$sciname = "";
// World Spider Catalog query string.
$wsc = '<p><a href="http://www.wsc.nmbe.ch/search?sFamily=&fMt=begin&sGenus=GGG&gMt=exact&sSpecies=SPSPSP&sMt=exact&multiPurpose=slsid&mMt=begin&searchSpec=s" target="_blank">World Spider Catalog lookup</a></p><!--adds-->';

// Get taxonomy info.
if (!empty($record['etaxonomy:MulMultiMediaRef_tab'])) {
  foreach ($record['etaxonomy:MulMultiMediaRef_tab'] as $taxonomy_record) {
    $genus = $taxonomy_record['ClaGenus'];
    $species = $taxonomy_record['ClaSpecies'];
    $authorstring = $taxonomy_record['AutAuthorString'];
    $sciname = $genus . " " . $species;
    // construct World Spider Catalog query string
    $wsc = str_replace('GGG', $genus, $wsc);
    $wsc = str_replace('SPSPSP', $species, $wsc);
  }
}

// SUBSET INFO
// We need to check each subset to make sure we have data for each 
// before we display the link on the page. That means querying the multimedia
// using the MulOtherNumber_tab and DetSubject_tab fields.
$subset_check = array(
  'male' => FALSE,
  'female' => FALSE,
  'habitus' => FALSE,
  'genitalia' => FALSE,
  'palp' => FALSE,
  'epigynum' => FALSE
);

// SUBSET INFO -- checking each category.
foreach ($subset_check as $key => $value) {
  $subset_terms = new IMuTerms();
  $subset_terms->add('MulOtherNumber_tab', $taxo_irn);
  $subset_terms->add('DetSubject_tab', $key);
  $subset_hits = $module->findTerms($subset_terms);
  $subset_results = $module->fetch('start', 0, -1, 'irn');
  $subset_count = $subset_results->count;
  ($subset_count > 0) ? $subset_check[$key] = TRUE : $subset_check[$key] = FALSE;
}

// SUBSET INFO -- constructing subset unordered list.
$subset_list_items = "";

foreach ($subset_check as $key => $value) {
  if ($value) {
    $subset_list_items .= "<li><a href=\"subset.php?irn=$taxo_irn&amp;flag=$key\">$key</a></li>";
  }
}

// Adding all images link.
$subset_list_items .= "<li><a href=\"subset.php?irn=$taxo_irn&amp;flag=\">all images</a></li>";

// Adding collection record link
$collrecd = "";
if (!empty($record['RelRelatedMediaRef_tab'][0])) {
    if ($record['RelRelatedMediaRef_tab'][0]['MulMimeType'] == "x-url" && !empty($record['RelRelatedMediaRef_tab'][0]['MulIdentifier'])) {
        $collection_record_link = $record['RelRelatedMediaRef_tab'][0]['MulIdentifier'];
        if ($collection_record_link) {
        $collrecd = ' <span class="view-collection-record"><a href="' . $collection_record_link . '" target="_blank">View collection record</a></span>';
        }
    }
}

// Get the associated rights info.
$r = "";
foreach ($record['DetMediaRightsRef'] as $r_record) {
  $r = $r_record;
  $r = str_replace('CC','<a href="https://creativecommons.org/licenses/by-nc/2.0/" target="_blank">CC',$r);
  $r = str_replace("NC","NC</a> (Attribution-NonCommercial)",$r);
}
$r = str_replace('[(c)', '[c]',$r);
$r = str_replace('] - Usage, Current','',$r);

// Build the filepath to image.
$multimedia_url = "";
$multimedia_url = '/' . substr($irn_string, -3, 3) . $multimedia_url;
$irn_string = substr_replace($irn_string, '', -3, 3);
$multimedia_url = "/" . $irn_string . $multimedia_url;
$multimedia_url = 'http://cornelia.fieldmuseum.org' . $multimedia_url . '/' . $record['MulIdentifier'];

// get the tpl
$page = file_get_contents('tpl-detail2.html');

// get the lookup file(s)
$lookup_bold = file_get_contents('lookup-bold.txt');


// swap in the vars
  $page= str_replace('{thisspecies}', $sciname, $page);
  $page= str_replace('{multitle}', $imgtitle, $page);
  $page= str_replace('{thiscredit}', $thiscredit, $page);
  $page= str_replace('{rrights}', $r, $page);
  $page= str_replace('{multimedia_url}', $multimedia_url, $page);
  $page= str_replace('{rbar}', $sciname, $page);
  $page= str_replace('{authorstring}', $authorstring, $page);
  $page = str_replace('{subset_list_items}', $subset_list_items, $page);
  // add link(s) based on successful lookup
  if (@strpos($lookup_bold,$sciname) !== false) {
    //add a link
    $mysuffix = $sciname;
    $mysuffix = str_replace(' ', '+', $mysuffix);
    $insert = '<p><a href="http://www.boldsystems.org/index.php/TaxBrowser_TaxonPage?taxon=' . $mysuffix . '" target="_blank">';
    $insert = $insert . 'BOLD systems taxon page</a></p><!--adds-->';
    $page= str_replace('<!--adds-->', $insert, $page); //IRL make this safer
  }
  // add collection record, if any
  $page= str_replace('<!--collrecd-->',$collrecd, $page); //IRL make this safer
  // add link to WSC
  $page= str_replace('<!--adds-->', $wsc, $page); //IRL make this safer
    
// write it out
print $page;

?>