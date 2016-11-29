<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Catalog;

class CatalogController extends Controller
{
    /**
     * Handles the individual Catalog page control.
     *
     * @param int $irn
     *  The IRN of the EMu Catalogue record.
     *
     * @return view
     */
    public function showCatalog($irn)
    {
        $catalog = new Catalog();
        $record = $catalog->getRecord($irn);

        $view = view('catalog-detail', [
            'record' => $record,
        ])->render();

        return $view;
    }
}
