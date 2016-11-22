<?php

/**
 * This configuration file is for all EMu related config for the LinEpig website.
 */

return [
    'emuserver' => '10.20.1.71',
    'emuport' => 40107,
    'multimedia_server' => 'cornelia.fieldmuseum.org',
    'home_multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'MulMimeType', 'thumbnail'
    ],
    'multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'MulOtherNumber_tab',
        'DetMediaRightsRef.(SummaryData)',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
        'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
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
    'search_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'MulOtherNumber_tab',
        'DetSubject_tab', 'thumbnail', 'DetMediaRightsRef.(SummaryData)', 'MulDescription',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
        'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
    ],
];
