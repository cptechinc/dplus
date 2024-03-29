<?php namespace Controllers\Mwm\Inventory\Mlot;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// Dplus Model
use InvLotMaster;
// Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;
use Dplus\DocManagement\Copier;
use Dplus\DocManagement\Folders;
// Dplus Filters
use Dplus\Filters;
// Dplus Inventory
use Dplus\Wm\Inventory\Search;
use Dplus\Wm\Inventory\Mlot\Img as ImgManager;
use Dplus\Wm\Inventory\Lotm;
// Mvc Controllers
use Controllers\Wm\Base;

class Img extends Base {
	const DPLUSPERMISSION = 'wm';
	const TITLE = 'Lot Images';

	private static $docm;

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
		self::pw('page')->headline = self::TITLE;
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/scan/.js.twig');
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
		$lotm = Lotm::getInstance();
		$filter = new Filters\Min\LotMaster();

		if ($lotm->exists($data->scan)) {
			self::pw('session')->redirect(self::lotserialUrl($data->scan), $http301 = false);
		}
		$filter->search($data->scan);
		$lots = $filter->query->paginate(self::pw('input')->pageNum, 15);
		$filter->query->find();

		self::initHooks();
		self::pw('page')->headline = "Searching for $data->scan";
		$html = self::displayScanResults($data, $lots);
		self::getImg()->deleteResponse();
		return $html;
	}

	private static function lotserial($data) {
		$lotm = Lotm::getInstance();
		$lot = $lotm->lot($data->lotserial);
		self::copyImage($data);

		self::initHooks();
		self::pw('page')->headline = "Lotserial #$data->lotserial";
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/lotserial/.js.twig');
		self::pw('config')->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		$html = self::displayLotserial($data, $lot);
		self::getImg()->deleteResponse();
		return $html;
	}

	private static function copyImage($data) {
		$docm = self::getDocm();

		if ($docm->hasImage($data->lotserial)) {
			$file = $docm->getImage($data->lotserial);
			$folder = Folders::getInstance()->folder($file->folder);
			$copier = Copier::getInstance();
			$copier->useDocVwrDirectory();

			if ($copier->isInDirectory($file->filename) === false) {
				$copier->copyFile($folder->directory, $file->filename);
			}
		}
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

	private static function displayScanResults($data, ModelPager $lots) {
		$docm = self::getDocm();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/scan/results/display.twig', ['docm' => $docm, 'lots' => $lots]);
		return $html;
	}

	private static function displayLotserial($data, InvLotMaster $lot) {
		$docm = self::getDocm();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::pw('config')->twig->render('warehouse/inventory/mlot/img/lotserial/display.twig', ['docm' => $docm, 'lot' => $lot]);
		return $html;
	}

	private static function displayResponse($data) {
		$imgM = self::getImg();
		$response = $imgM->getResponse();

		if (empty($response) || $response->hasSuccess()) {
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

		$m->addHook('Page(pw_template=whse-mlot)::imgUrl', function($event) {
			$event->return = Menu::imgUrl();
		});
	}

	public static function getDocm() {
		if (empty(self::$docm)) {
			self::$docm = new Docm();
		}
		return self::$docm;
	}

	public static function getImg() {
		return ImgManager::getInstance();
	}
}
