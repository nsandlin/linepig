<?php

namespace Linepig\Http\Controllers;

use Linepig\Http\Controllers\Controller;

class HomeController extends Controller
{
	/**
	 * Handles the homepage photo grid view.
	 *
	 */
	public function showPhotos()
	{
		return view('homepage');
	}
}
