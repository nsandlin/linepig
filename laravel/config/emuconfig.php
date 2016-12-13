<?php

/**
 * This configuration file is for all EMu related config for the LinEpig website.
 */

return [
    'emuserver' => '10.20.1.71',
    'emuport' => 40107,
    'website_domain' => 'http://linepig.fieldmuseum.org/',
    'website_links' => [
        'http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery',
        'http://blogs.scientificamerican.com/guest-blog/internet-porn-fills-gap-in-spider-taxonomy/',
        'http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help',
        'https://github.com/nsandlin/linepig'
    ],
    'multimedia_server' => 'cornelia.fieldmuseum.org',
    'BOLD_import_url' => 'http://boldsystems.org/index.php/TaxBrowser_TaxonPage/SpeciesSummary?taxid=1266',
    'home_multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'MulMimeType', 'thumbnail'
    ],
    'multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'MulOtherNumber_tab',
        'DetMediaRightsRef.(SummaryData)', 'NteText0',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
        'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
        '<ecatalogue:MulMultiMediaRef_tab>.(
            irn, SummaryData, MulMultiMediaRef_tab.(irn, thumbnail)
         )',
        'MulOtherNumberSource_tab',
    ],
    'catalog_fields' => [
        'irn', 'SummaryData', 'DarGenus', 'DarSpecies', 'DarCatalogNumber',
        'LotTotalCount', 'LotSemaphoront_tab', 'LotWetCount_tab',
        'IdeIdentifiedByRef_nesttab.(SummaryData)', 'IdeDateIdentified0',
        'ColCollectionEventRef.(
            irn, SummaryData, ColCollectionMethod, ColCollectionEventCode,
            ColDateVisitedFrom, ColDateVisitedTo, ColParticipantRef_tab.(SummaryData),
            ColSiteRef.(irn),
         )',
         'DarLatitude', 'DarLongitude', 'DarMinimumElevation',
         'MulMultiMediaRef_tab.(irn, MulTitle, MulDescription, thumbnail)'
    ],
    'site_fields' => [
        'irn', 'SummaryData', 'AquHabitat_tab',
    ],
    'subsets_to_check' => [
        'male' => false,
        'female' => false,
        'habitus' => false,
        'genitalia' => false,
        'palp' => false,
        'epigynum' => false
    ],
    'subset_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'MulMimeType', 'thumbnail',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
    ],
    'rights_cc' => '<a href="https://creativecommons.org/licenses/by-nc/2.0/" target="_blank">CC',
    'rights_nc' => 'NC</a> (Attribution-NonCommercial)',
    'search_keywords' => [
        'male',
        'female',
        'habitus',
        'genitalia',
        'palp',
        'epigynum',
    ],
    'search_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'MulOtherNumber_tab',
        'DetSubject_tab', 'thumbnail', 'DetMediaRightsRef.(SummaryData)', 'MulDescription',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
        'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
    ],
];
