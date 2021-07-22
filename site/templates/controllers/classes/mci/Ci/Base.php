<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// MVC Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {

	static public function ciUrl($custID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ci-customer')->url);
		if ($custID) {
			$url->query->set('custID', $custID);
		}
		return $url->getUrl();
	}

	static public function ciShiptoUrl($custID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('shiptos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $custID);
		}
		return $url->getUrl();
	}
}
