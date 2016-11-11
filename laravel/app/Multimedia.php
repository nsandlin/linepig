<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Multimedia extends Model
{
    public function __construct() { }

    /**
     * Processes and fixes the Multimedia record title for our purposes.
     *
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *  Returns a string of the new title
     */
    public static function fixSpeciesTitle($record)
    {
        $title = $record['MulTitle'];
        $newTitle = str_replace(" epigynum", "", $title);

        return $newTitle;
    }

    /**
     * Alters the Multimedia URL so we have a proper URL reference to the file
     * on the Multimedia server.
     *
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *   Returns string with the corrected URL
     */
    public static function fixThumbnailURL($record)
    {
        $irn = $record['irn'];
        $filename = $record['thumbnail']['identifier'];
        $url = "";
        $url = "/" . substr($irn, -3, 3) . $url;
        $irn = substr_replace($irn, '', -3, 3);
        $url = "/" . $irn . $url;

        $url = "http://" . config('emuconfig.multimedia_server') . $url .
                 "/" . $record['thumbnail']['identifier'];

        return $url;
    }
}
