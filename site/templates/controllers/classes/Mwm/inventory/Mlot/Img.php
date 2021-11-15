<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;
// Dplus Inventory Search
use Dplus\Wm\Inventory\Search;
// Dplus CRUD
use Dplus\Wm\Inventory\Mlot\Img as ImgManager;
// Mvc Controllers
use Controllers\Wm\Base;

class Img extends Base {
	const DPLUSPERMISSION = 'wm';
	const TITLE = 'Lot Images';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['scan|text', 'lotserial|text', 'action|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if (empty($data->scan) === false) {
			return self::scan($data);
		}
		if (empty($data->lotserial) === false) {
			return self::lotserial($data);
		}
		return self::displayInitialScreen($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['scan|text', 'lotserial|text', 'action|text']);
		$url = Menu::imgUrl();
		$manager = self::getImg();
		$success = $manager->process(self::pw('input'));
		
		switch ($data->action) {
			case 'update':
				if ($success === false) {
					$url = self::lotserialUrl($data->lotserial);
				}
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function scan($data) {
		Search::getInstance()->requestSearch($data->scan);
		self::initHooks();
		return self::displayScanResults($data);
	}

	private static function lotserial($data) {
		Search::getInstance()->requestSearch($data->lotserial);
		self::initHooks();
		self::pw('page')->headline = "Lot #$data->lotserial";
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/lotserial/.js.twig');
		self::pw('config')->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayLotserial($data);
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayScanForm($data) {
		return self::pw('config')->twig->render('warehouse/inventory/mvc/form.twig');
	}

	private static function displayInitialScreen($data) {
		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::displayScanForm($data);
		return $html;
	}

	private static function displayScanResults($data) {
		$inventory = Search::getInstance();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/scan/results.twig', ['inventory' => $inventory]);
		return $html;
	}

	private static function displayLotserial($data) {
		$inventory = Search::getInstance();
		$lotserial = $inventory->getLotserial($data->lotserial);
		$docm = self::getDocm();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/lotserial/display.twig', ['lotserial' => $lotserial]);
		return $html;
	}

	private static function displayResponse($data) {
		$imgM = self::getImg();
		$response = $imgM->getResponse();

		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function lotserialUrl($lotserial) {
		$url = new Purl(Menu::imgUrl());
		$url->query->set('lotserial', $lotserial);
		return $url->getUrl();
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=whse-mlot)::lotserialUrl', function($event) {
			$event->return = self::lotserialUrl($event->arguments(0));
		});
	}

	public static function getDocm() {
		return new Docm();
	}

	public static function getImg() {
		return ImgManager::getInstance();
	}
}
