<?php

/**
 * This class is responsible for retrieving the old "backlink"
 * image for a LinEpig multimedia record.
 *
 * The way to retrieve this information is by retrieving the Other #
 * from the Multimedia module record where the Other # Source is
 * "etaxonomy irn".
 *
 * From there, query the Taxonomy module record and retrieve the
 * REVERSE attached Narrative record. The Narrative record will be
 * classified as a "correction". The Multimedia image is attached
 * to this Narrative record.
 */
class BacklinkImage
{
    /**
     * Object variables
     */
    protected $taxonomyIRN;
    protected $narrativeIRN;
    protected $oldImageRecord;
    protected $oldImageURL;
    
    /**
     * initializes the object and calls all of the appropriate functions.
     *
     */
    public function __construct($irn)
    {
        $this->taxonomyIRN = $this->getTaxonomyIRNFromMultimedia($irn);
        $this->narrativeIRN = $this->getReversedAttachedNarrativeIRN($this->taxonomyIRN);
        $this->oldImageRecord = $this->getOldImageRecord($this->narrativeIRN);
        $this->setFormattedImageURL($this->oldImageRecord);
    }

    /**
     * Gets the Taxonomy record IRN listed in the Other # section.
     * The Source must be listed as "etaxonomy irn" in order to be
     * retrieved correctly.
     *
     * @param int $irn
     *   The IRN of the Multimedia record.
     *
     * @return int
     *   Returns an integer of the Taxonomy IRN.
     */
    public function getTaxonomyIRNFromMultimedia($irn)
    {
        $session = new IMuSession(EMU_IP, EMU_PORT);
        $module = new IMuModule('emultimedia', $session);
        $terms = new IMuTerms();
        $terms->add('irn', $irn);
        $hits = $module->findTerms($terms);
        $columns = array('irn', 'MulOtherNumber_tab', 'MulOtherNumberSource_tab');
        $results = $module->fetch('start', 0, 1, $columns);
        $record = $results->rows[0];
        $sourceKey = null;

        // Loop through the Other Number Source table to get the etaxonomy IRN.
        foreach ($record['MulOtherNumberSource_tab'] as $key => $value) {
            if ($value == 'etaxonomy irn') {
                $sourceKey = $key;
            }
        }
        
        // If we don't have an etaxonomy irn source key, return null.
        if (is_null($sourceKey)) {
            return null;
        }
        
        // Let's now grab the Taxonomy record IRN.
        
        if (!empty($record['MulOtherNumber_tab'][$sourceKey])) {
            return $record['MulOtherNumber_tab'][$sourceKey];
        }
        else {
            return null;
        }
    }
    
    /**
     * Gets the reverse-attached Narrative IRN, from a Taxonomy record.
     *
     * @param int $irn
     *   The IRN of the Taxonomy record.
     *
     * @return int
     *   Returns the IRN of the Narrative record.
     */ 
    public function getReversedAttachedNarrativeIRN($irn)
    {
        $session = new IMuSession(EMU_IP, EMU_PORT);
        $module = new IMuModule('etaxonomy', $session);
        $terms = new IMuTerms();
        $terms->add('irn', $irn);
        $hits = $module->findTerms($terms);
        $columns = array('irn', '<enarratives:TaxTaxaRef_tab>.irn');
        $results = $module->fetch('start', 0, 1, $columns);
        $record = $results->rows[0];
        
        if (!empty($record['enarratives:TaxTaxaRef_tab'][0]['irn'])) {
            return $record['enarratives:TaxTaxaRef_tab'][0]['irn'];
        }
        else {
            return null;
        }
    }
    
    /**
     * Gets the Multimedia image, attached to the Narrative record.
     * We're grabbing ONLY the first image.
     *
     * @param int $irn
     *   The IRN of the Narrative record.
     *
     * @return array
     *   Return an array of the Narrative record.
     */
    public function getOldImageRecord($irn)
    {
        $session = new IMuSession(EMU_IP, EMU_PORT);
        $module = new IMuModule('enarratives', $session);
        $terms = new IMuTerms();
        $terms->add('irn', $irn);
        $hits = $module->findTerms($terms);
        $columns = array('irn', 'images.(irn, MulIdentifier)');
        $results = $module->fetch('start', 0, 1, $columns);
        $record = $results->rows[0];
        
        if (!empty($record['images'][0])) {
            return $record['images'][0];
        }
        else {
            return null;
        }
    }
    
    /**
     * Retrieves the old image URL.
     *
     * @return string
     *   The URL of the old image.
     */
    public function getFormattedImageURL()
    {
        return $this->oldImageURL;
    }
    
    /**
     * Fixes the Multimedia image URL, to be properly formatted link to the web server.
     *
     * @param array $imageRecord
     *   The array of the Multimedia record.
     *
     * @return string
     *   Returns a string of the Multimedia image.
     */
    public function setFormattedImageURL($imageRecord)
    {
        $url = "/" . substr($imageRecord['irn'], -3, 3);
        $irn = substr_replace($imageRecord['irn'], '', -3, 3);
        $url = "/" . $irn . $url;
        $url = 'http://cornelia.fieldmuseum.org' . $url . "/" . $imageRecord['MulIdentifier'];
        
        $this->oldImageURL = $url;
    }
}
