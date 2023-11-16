<?php

/**
 * This configuration file is for all EMu related config for the LinEpig website.
 */

return [
    'cache_ttl' => 86400, // 1 day
    'homepage_pagination_per_page' => 105,
    'homepage_days_ago_for_recent_records' => 90,
    'mongodb_conn_options' => ["typeMap" => ['root' => 'array', 'document' => 'array']],
    'mongodb_search_docs_fields_to_exclude' => [
        'XmpMetadata', 'DocImageType', 'DocNumberPages', 'DocBitsPerPixel', 'MulMultimediaCreatorRef',
        'DetMediaRightsRef', 'DocNumberColours', 'MulMultimediaCreatorRefLocal1', 'DocFileSize',
        'DocColourSpace', 'rownum', 'DocHeight', 'MulMultimediaCreatorRefLocal0', 'ExiTag',
        'DocPlanes', 'ChaImageColorDepth', 'RelIsParent', 'MulMimeFormat', 'AdmGUIDIsPreferred',
        'SecCanDisplay', 'ExiIfd', 'ChaFileSize', 'DocCompression', 'IptValue', 'MulHasMultimedia',
        'DocQuality', 'ExiValue', 'ChaImageHeight', 'IptTag', 'DetMediaRightsRefLocal', 'MulOtherNumber',
        'RepositoryEmpty', 'DocWidth', 'DocMimeFormat', 'ChaImageResolution', 'DetBornDigitalFlag',
        'DocResolution', 'DocMimeType', 'MulDocumentType', 'ChaImageWidth', 'AdmGUIDType', 'DetResourceType',
        'SecCanDelete', 'ExtendedData'
    ],
    'emuserver' => 'ross.fieldmuseum.org',
    'emuport' => 40107,
    'website_domain' => 'https://linepig.fieldmuseum.org/',
    'website_links' => [
        'http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery',
        'http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery/can-you-help',
    ],
    'database_filename' => 'linepig.sqlite',
    'multimedia_server' => 'fm-digital-assets.fieldmuseum.org', // MM referenced at full Ross IP address
    'BOLD_import_url' => 'http://boldsystems.org/index.php/TaxBrowser_TaxonPage/SpeciesSummary?taxid=1266',
    'home_multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'MulMimeType', 'thumbnail'
    ],
    'multimedia_fields' => [
        'irn', 'MulIdentifier', 'MulTitle', 'DetSource', 'MulOtherNumber_tab',
        'AdmPublishWebNoPassword', 'DetMediaRightsRef.(SummaryData)', 'NteText0', 'NteType_tab',
        'MulMultimediaCreatorRef_tab.(NamPartyType, ColCollaborationName)',
        '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus, ClaSpecies, AutAuthorString)',
        'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
        '<ecatalogue:MulMultiMediaRef_tab>.(
            irn, SummaryData, DarGlobalUniqueIdentifier, MulMultiMediaRef_tab.(irn, thumbnail)
         )',
        'MulOtherNumberSource_tab',
    ],
    'catalog_fields' => [
        'irn', 'SummaryData', 'DarGenus', 'DarSpecies', 'DarCatalogNumber',
        'AdmPublishWebNoPassword', 'AdmGUIDValue_tab', 'AdmGUIDIsPreferred_tab',
        'LotTotalCount', 'PheStage_tab', 'PheSex_tab', 'PreCount_tab', 'PrePrepType_tab',
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
        'MulOtherNumberSource_tab',
    ],
];
