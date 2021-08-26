<?php namespace Controllers\Misc\Cart;
// Purl URI Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Cart extends AbstractController {
/* =============================================================
	URLs
============================================================= */
	public static function setCustomerUrl($custID, $shiptoID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=cart')->url);
		$url->query->set('custID', $custID);
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}
}
