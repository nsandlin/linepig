<?php
    
// Enable all error reporting.
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Setting up requirements.
$lib = "imu-api-php";
require_once $lib . '/IMu.php';
require_once IMu::$lib . '/Session.php';
require_once IMu::$lib . '/Module.php';
require_once IMu::$lib . '/Terms.php';

// Get query string.
$irn = filter_var($_GET['irn'], FILTER_VALIDATE_INT);

// Create a Session and selecting the module we want to query.
$session = new IMuSession('10.20.1.71', 40107);
$module = new IMuModule('emultimedia', $session);

// Adding our search terms.
$terms = new IMuTerms();
$terms->add('irn', $irn);

// Fetching results.
$hits = $module->findTerms($terms);
$columns = array('irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'NotNotes','<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies,irn)'); 
$results = $module->fetch('start', 0, 1, $columns);
$record = $results->rows[0];
$irn_string = $irn;
$irn_length = strlen($irn_string);
$num_of_divisions = $irn_length / 3;
$multimedia_url = "";
$taxo_irn = $record['NotNotes'];
$sciname = "";
$wsc = '</p><p><a href="http://www.wsc.nmbe.ch/search?sFamily=&fMt=begin&sGenus=GGG&gMt=exact&sSpecies=SPSPSP&sMt=exact&multiPurpose=slsid&mMt=begin&searchSpec=s" target="_blank">World Spider Catalog lookup</a></p></td>';

//$genus =  $record['etaxonomy:MulMultiMediaRef_tab'][0]['ClaGenus'];
      foreach ($record['etaxonomy:MulMultiMediaRef_tab'] as $taxonomy_record) {
        $genus = $taxonomy_record['ClaGenus'];
        $species = $taxonomy_record['ClaSpecies'];
        $sciname = $genus . " " . $species;
        // construct World Spider Catalog query string
        $wsc = str_replace('GGG', $genus, $wsc);
        $wsc = str_replace('SPSPSP', $species, $wsc);
      }


$multimedia_url = "";
$multimedia_url = '/' . substr($irn_string, -3, 3) . $multimedia_url;
$irn_string = substr_replace($irn_string, '', -3, 3);
$multimedia_url = "/" . $irn_string . $multimedia_url;
$multimedia_url = 'http://cornelia.fieldmuseum.org' . $multimedia_url . '/' . $record['MulIdentifier'];




// Taxonomy query
// Create a Session and selecting the module we want to query.
$tax_session = new IMuSession('10.20.1.71', 40107);
$tax_module = new IMuModule('etaxonomy', $tax_session);

// Adding our search terms.
$tax_terms = new IMuTerms();
$tax_terms->add('irn', $record['NotNotes']);

// Fetching results.
$tax_hits = $tax_module->findTerms($tax_terms);
$tax_columns = array('ClaGenus', 'ClaSpecies', 'AutAuthorString'); 
$tax_results = $tax_module->fetch('start', 0, 1, $tax_columns);
$tax_record = $tax_results->rows[0];

// vars
$thisspecies = $record['MulTitle'];
$thiscredit = $record['DetSource'];
$authorstring = $tax_record['AutAuthorString'];

// get the tpl
$page = file_get_contents('tpl-detail.html');

// get the lookup file(s)
$lookup_bold = file_get_contents('lookup-bold.txt');



// swap in the vars
    $page= str_replace('{thisspecies}', $sciname, $page);
    $page= str_replace('{thiscredit}', $thiscredit, $page);
    $page= str_replace('{multimedia_url}', $multimedia_url, $page);
    $page= str_replace('{rbar}', $sciname, $page);
    $page= str_replace('{authorstring}', $authorstring, $page);
    $page= str_replace('{taxoirn}', $taxo_irn, $page);
    // add link(s) based on lookup
    if (@strpos($lookup_bold,$sciname) !== false) {
      //add a link
      $mysuffix = $sciname;
      $mysuffix = str_replace(' ', '+', $mysuffix);
      $insert = '</p><p><a href="http://www.boldsystems.org/index.php/TaxBrowser_TaxonPage?taxon=' . $mysuffix . '" target="_blank">';
      $insert = $insert . 'BOLD systems taxon page</a></p></td>';
      $page= str_replace('</p></td>', $insert, $page); //IRL make this safer
    }
    // add link to WSC
    $page= str_replace('</p></td>', $wsc, $page); //IRL make this safer
    
// write it out
print $page;

?>