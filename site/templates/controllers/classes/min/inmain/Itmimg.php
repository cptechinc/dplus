<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// Dplus Filters
use Dplus\Filters;
// Document Management
use Dplus\DocManagement\Finders\It\Img as Docm;
use Dplus\DocManagement\Copier;
use Dplus\DocManagement\Folders;
// Dplus CRUD
use Dplus\Min\Inmain\Itmimg as ImgManager;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Min\Base;

class Itmimg extends Base {
	const DPLUSPERMISSION = '';
	private static $docm;

	public static function index($data) {
		$fields = ['itemID|text', 'q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if (self::validateUserPermission() === false) {
			return self::displayAlertUserPermission($data);
		}
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->itemID) === false) {
			return self::item($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
		$imgM = self::getImg();
		$imgM->process(self::pw('input'));
		
		self::pw('session')->redirect(self::imgUrl(), $http301 = false);
	}

	public static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);

		$filter = new Filters\Min\ItemMaster();

		if ($data->q) {
			self::pw('page')->headline = "Items: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby(self::pw('page'));

		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::initHooks();
		$html = self::displayList($data, $items);
		return $html;
	}

	public static function item($data) {
		$mItm = self::pw('modules')->get('Itm');

		if ($mItm->exists($data->itemID) === false) {
			self::pw('session')->redirect(self::itmimgUrl(), $http301 = false);
		}
		self::copyImage($data);

		self::pw('page')->headline = "Item Image: #$data->itemID";
		self::initHooks();
		$item = $mItm->item($data->itemID);
		self::pw('page')->js .= self::pw('config')->twig->render('min/inmain/itmimg/item/.js.twig');
		return self::displayItem($data, $item);
	}


/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $items) {
		$docm   = self::getDocm();
		$config = self::pw('config');

		$html  = '';
		// $html .= self::breadCrumbsDisplay($data);
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('min/inmain/itmimg/list/display.twig', ['items' => $items, 'docm' => $docm]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $items]);
		return $html;
	}

	private static function displayItem($data, ItemMasterItem $item) {
		$docm   = self::getDocm();
		$config = self::pw('config');

		$html  = '';
		$html .= $config->twig->render('min/inmain/itmimg/item/display.twig', ['item' => $item, 'docm' => $docm]);
		return $html;
	}

	private static function copyImage($data) {
		$docm = self::getDocm();

		if ($docm->hasImage($data->itemID)) {
			$file = $docm->getImage($data->itemID);
			$folder = Folders::getInstance()->folder($file->folder);
			$copier = Copier::getInstance();
			$copier->useDocVwrDirectory();

			if ($copier->isInDirectory($file->filename) === false) {
				$copier->copyFile($folder->directory, $file->filename);
			}
		}
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
	public static function imgUrl() {
		return self::pw('pages')->get('pw_template=min-itmimg')->url;
	}

	public static function itemUrl($itemID) {
		$url = new Purl(self::imgUrl());
		$url->query->set('itemID', $itemID);
		return $url->getUrl();
	}


/* =============================================================
	Supplemental Functions
============================================================= */
	public static function getImg() {
		return ImgManager::getInstance();
	}

	public static function getDocm() {
		if (empty(self::$docm)) {
			self::$docm = new Docm();
		}
		return self::$docm;
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=min-itmimg)::itemUrl', function($event) {
			$event->return = self::itemUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=min-itmimg)::imgUrl', function($event) {
			$event->return = self::imgUrl($event->arguments(0));
		});
	}
}
