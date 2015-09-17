#####################
LINEPIG DOCUMENTATION
#####################

Main debugging issues
1) Multiple calls to get Taxonomy information.
2) Subset file, reverse attachments don't appear to work in drilldown.

LinEPig Notes
Main page
- Responsive design -- flexbox -- how many photos per row.
- index.php -- EMu field -- primary field for first photo to display on main page.

Detail page
- Nina adding Rights.
- Exported records of LinEpig using test script.
- Switch from BOLD lookup file to BOLD API.

Subset page
- AKA newview.php
- Pete look at taxonomy query; if sciname is blank, add another query. Look
  to see if I can fix the initial query.
- Under the "Show all" links, items that have nothing available should have
  their links disappear. Add IDs to the list items.


http://linepig.fieldmuseum.org/            temporary main page during dev
http://linepig.fieldmuseum.org/demo.php    latest version of main page - public
http://linepig.fieldmuseum.org/main0c.php  version we work on, copy to 'demo' when safe


.......................................
####  SCRIPT NOTES  ####

main0c.php - thumbnails (LinEpig main page <=> demo.php)
html display template: internal
var to display:        $display
display type:          thumbnails (all)
thumbnail links call:  newpage.php


newpage.php - detail (called from main page) --- new naming detail.php
html display template: tpl-detail.html
var to display:        (multiple)
display type:          detail
lookup page(s):        lookup-bold.txt (BOLD Systems; IRL could use API?)    
thumbnail links call:  should go to DNG where available
filter links call:     newview.php -- new naming subset.php


newview.php - thumbnails
html display template: internal
var to display:        $display
display type:          thumbnails (filtered subset)


newpage2.php - detail (called from filtered subset)
html display template: tpl-detail2.html
var to display:        (multiple)
display type:          detail
lookup page(s):        (same as newpage.php)    
thumbnail links call:  (same as newpage.php)
filter links call:     (same as newpage.php)




.......................................
####  SERVER NOTES  ####

http://spice.fieldmuseum.org      location of app (scripts & templates)
http://cornelia.fieldmuseum.org   location of multimedia objects   