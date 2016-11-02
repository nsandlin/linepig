#####################
LINEPIG DOCUMENTATION
#####################

Field Museum scripts using IMu API to display images and metadata from the Field Museum's KE EMu database. 
IMu API for PHP - http://imu.mel.kesoftware.com/doc/api/php
KE Software's KE EMu - http://emu.kesoftware.com/
About LinEpig - http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery

.......................................
####  CURRENT TASKS  ####

Detail pages
- Add links to external collection record at lending institution
- Switch from BOLD lookup file to BOLD API?
- Under the "Show all" (links to Subset views), links to empty subsets 
  should have disappear. How to determine if filter would return anything?
  

.......................................
####  SCRIPT NOTES  ####

index.php - thumbnails of all epigyna images (LinEpig main page)
html display template: internal
var to display:        $display
display type:          thumbnails (all)
thumbnail links call:  detail.php

detail.php - single image with info, called from index.php
html display template: tpl-detail.html
var to display:        (multiple)
display type:          detail
lookup page(s):        lookup-bold.txt (BOLD Systems; IRL could use API?)    
thumbnail links call:  should go to DNG where available
filter links call:     subset.php

subset.php - thumbnails of a related group of images
html display template: internal
var to display:        $display
display type:          thumbnails (filtered subset)

detail2.php - single image with info; called from subset.php
html display template: tpl-detail2.html
var to display:        (multiple)
display type:          detail
lookup page(s):        (same as detail.php)    
thumbnail links call:  (same as detail.php)
filter links call:     (same as detail.php)


.......................................
####  LIVE SITE  ####

http://linepig.fieldmuseum.org/            temporary main page during dev
http://linepig.fieldmuseum.org/demo.php    latest version of main page - public
