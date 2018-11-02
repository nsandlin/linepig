#####################
LINEPIG DOCUMENTATION
#####################

Field Museum app using IMu API and Laravel to display images and metadata from the Field Museum's KE EMu database.
Laravel - https://laravel.com/
IMu API for PHP - http://imu.mel.kesoftware.com/doc/api/php
KE Software's KE EMu - http://emu.kesoftware.com/
About LinEpig - http://www.fieldmuseum.org/science/special-projects/dwarf-spider-id-gallery
LinEpig - http://linepig.fieldmuseum.org/ 

.......................................
####  RUNNING DOCKER LOCALLY  ####
- Be sure you have your .env file set up
- Run these commands to build and run the container (in Windows 10)
- BE SURE TO INSTALL YOUR COMPOSER REQUIREMENTS LOCALLY FIRST, run "composer install" in the Laravel directory

### BUILD IT ###
docker build -t nsandlin/linepig:1.0 .

### RUN IT (Be sure to change volume sharing to your local Laravel directory)  ###
### this, like all Docker cmds need to be run from where Dockerfile lives ###
docker run -it -d -p 8080:80 --name linepig -v C:/Users/Nina/Sites/linepig/laravel:/var/www/html nsandlin/linepig:1.0

If you get errors running the container, stop all Docker containers:
docker stop $(docker ps -aq)

Then, quit Docker and start it back up again. Run the Docker run command again.

Visit the website here: http://localhost:8080/

You can SSH into the container  (Windows 10 needs winpty):
# Get the docker container name or ID
$ docker ps 

# SSH into the container
$ winpty docker exec -it linepig //bin//sh
.......................................
####  CONCEPTUAL STRUCTURE  ####

Currently three main page types:
Home   - thumbnails of epigynum of each species available
Detail - large view of epigynum, with links to related images, taxonomic references, and collection record if available
Subset - home-like page of images selected from Detail links or Search


.......................................
####    IMPLEMENTATION    ####

Laravel 5.3, PHP 7

MODELS (/app/)
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
Database: /config/database.php
database_path: /database/linepig.sqlite
styling: Laravel Eloquent ORM
