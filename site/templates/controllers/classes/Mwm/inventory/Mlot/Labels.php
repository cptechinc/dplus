<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use InvLot;
// Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;
use Dplus\DocManagement\Copier;
use Dplus\DocManagement\Folders;
// Dplus Filters
use Dplus\Filters;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lotm;
use Dplus\Wm\Inventory\Mlot\Labels as Printer;


// Dplus CRUD

// Mvc Controllers
use Controllers\Wm\Base;

class Labels extends Base {
	const DPLUSPERMISSION = 'wm';
	const TITLE = 'Labels';

	private static $docm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['q|text', 'lotserial|text', 'action|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if (empty($data->lotserial) === false) {
			return self::lotserial($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text']);
		$printer  = self::getPrinter();
		$printer->process(self::pw('input'));
		self::pw('session')->redirect(Menu::labelsUrl(), $http301 = false);
	}

	private static function list($data) {
		self::initHooks();
		self::pw('page')->headline = self::TITLE;
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/mlot/labels/list/.js.twig');

		$filter = new Filters\Min\LotMaster();
		self::filterLots($data, $filter);

		$lots = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$html = self::displayList($data, $lots);
		self::getPrinter()->deleteResponse();
		return $html;
	}

	private static function filterLots($data, Filters\Min\LotMaster $filter) {
		$filter->inStock();
		
		self::sanitizeParametersShort($data, ['q|text', 'itemID|text', 'instock|bool']);

		if (empty($data->itemID) === false) {
			self::pw('page')->headline = "Filtering Lots for $data->itemID";
			$filter->query->filterByItemid($data->itemID);
		}

		if (empty($data->q) === false) {
			$lotm = Lotm::getInstance();

			if ($lotm->exists($data->q)) {
				self::pw('session')->redirect(self::lotserialUrl($data->q), $http301 = false);
			}
			self::pw('page')->headline = "Searching Lots for $data->q";
			$filter->search($data->q);
		}
	}

	private static function lotserial($data) {
		$lotm = Lotm::getInstance();

		if ($lotm->exists($data->lotserial) === false) {
			self::pw('session')->redirect(Menu::labelsUrl(), $http301);
		}
		self::pw('page')->headline = "Print Label for $data->lotserial";
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/mlot/labels/lotserial/.js.twig');

		$lot = $lotm->lot($data->lotserial);
		$html = self::displayLotserial($data, $lot);
		self::getPrinter()->deleteResponse();
		return $html;
	}


/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $lots) {
		$docm = self::getDocm();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/labels/list/display.twig', ['lots' => $lots, 'docm' => $docm]);
		return $html;
	}

	private static function displayLotserial($data, InvLot $lot) {
		$docm = self::getDocm();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/labels/lotserial/display.twig', ['lot' => $lot, 'docm' => $docm]);
		return $html;
	}

	private static function displayResponse($data) {
		$printer  = self::getPrinter();
		$response = $printer->getResponse();

		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function lotserialUrl($lotserial) {
		$url = new Purl(Menu::labelsUrl());
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

		$m->addHook('Page(pw_template=whse-mlot)::labelsUrl', function($event) {
			$event->return = Menu::labelsUrl();
		});
	}

	public static function getPrinter() {
		return Printer::getInstance();
	}

	public static function getDocm() {
		if (empty(self::$docm)) {
			self::$docm = new Docm();
		}
		return self::$docm;
	}
}
