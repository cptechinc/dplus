<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// MVC Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
/* =============================================================
	URLs
============================================================= */
	public static function ciUrl($custID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ci')->url);
		if ($custID) {
			$url->query->set('custID', $custID);
		}
		return $url->getUrl();
	}

	public static function ciShiptoUrl($custID, $shiptoID = '') {
		$url = new Purl(self::ciUrl($custID));
		$url->path->add('shiptos');
		if ($shiptoID) {
			$url->query->set('shiptoID', $custID);
		}
		return $url->getUrl();
	}

	public static function ciSubfunctionUrl($custID, $sub) {
		$url = new Purl(self::pw('pages')->get('pw_template=ci')->url);
		$url->path->add($sub);
		$url->query->set('custID', $custID);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayInvalidCustid($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID not found"]);
	}

	protected static function displayUserNotAllowedCustomer($data) {
		$config = self::pw('config');
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You do not have permission to access to $data->custID"]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('customers/search-form.twig');
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCio() {
		return self::pw('modules')->get('Cio');
	}
}
