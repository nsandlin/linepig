# LINEPIG DOCUMENTATION

Field Museum app using MongoDB and Laravel to display images and metadata from the Field Museum's KE EMu database.

[Laravel](https://laravel.com/docs/8.x)  
[KE Software's KE EMu](http://emu.kesoftware.com/)  
[About LinEpig](http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery)  
[LinEpig](http://linepig.fieldmuseum.org/)  

## Running the site locally

Docker is NO LONGER REQUIRED.  
Run this command from the base directory
`php artisan serve`

.lock files are now included in the repository, as they should be.  
Be sure to run `composer install` and then `npm install` to make sure you have your dependencies installed.

To run updates for your CSS/JS, execute this command: `npm run dev`.  
You can also watch your files for changes with: `npm run watch`.  

## Editing CSS/JS

There are new locations for these files.  
Please edit these file locations instead:  
`resources/css/app.css`  
`resources/js/app.js`  

If you are making changes to these files, be sure to run `npm run production` on the server after the
files are updated on GitHub so the changes are reflected on the website.

## Conceptual Structure

Currently three main page types:  
Home   - thumbnails of epigynum of each species available  
Detail - large view of epigynum, with links to related images, taxonomic references, and collection record if available  
Subset - home-like page of images selected from Detail links or Search

## MongoDB

[MongoDB PHP docs](https://www.mongodb.com/docs/php-library/v1.2/tutorial/)

MongoDB connection string info is kept in the `.env` file and should never be committed to the repo.

## Implementation

Laravel 8, PHP 7.4 or 8

MODELS (/app/Models)  
Multimedia.php grabs from EMu getRecord

CONTROLLERS (/app/Http/Controllers/)  
HomeController.php  
MultimediaController.php  
SearchController.php  

VIEWS (/resources/views/)  
catalog-detail.blade.php - displays collection record (for FMNH material)  
home.blade.php  
layout-for-individual-pages.blade.php - displays Detail page  
layout-home.blade.php  
search.blade.php  
search.results.blade.php - displays Subset if called from Search  
subset.blade.php - displays Subset if called from link  

Environment: /.env  
Database: /config/database.php (no longer used, only useful for default redis settings)  
styling: Laravel Eloquent ORM  
