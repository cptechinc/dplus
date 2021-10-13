<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use BomItem;
use BomComponent;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Mpm\Pmmain\Bmm as BmmManager;
// Mvc Controllers
use Controllers\Mpm\Base;

class Bmm extends Base {
	const DPLUSPERMISSION = 'bmm';
	private static $bmm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['bomID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->bomID) === false) {
			if (empty($data->component) === false) {
				return self::component($data);
			}
			return self::bom($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['bomID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::bomUrl($data->bomID);
		$bmm  = self::getBmm();

		if ($data->action) {
			$bmm->processInput(self::pw('input'));
		}

		if ($bmm->components->hasComponents($data->bomID) === false) {
			$url = self::bmmUrl();
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function bom($data) {
		$bmm  = self::getBmm();
		$page = self::pw('page');
		$page->headline = "BMM: $data->bomID";

		if ($data->bomID === 'new') {
			$page->headline = 'BMM: Creating new Bill of Material';
		}
		$bmm->lockrecord($data->bomID);
		$bom = $bmm->header->getOrCreate($data->bomID);
		self::initHooks();
		$html = self::displayBom($data, $bom);
		$bmm::deleteResponse();
		return $html;
	}

	private static function component($data) {
		$bmm  = self::getBmm();
		$page = self::pw('page');
		$page->headline = "BMM: $data->bomID Component $data->component";

		if ($data->component == 'new') {
			$page->headline = "BMM: $data->bomID Add Component";
		}
		$bmm->lockrecord($data->bomID);
		$bom = $bmm->header->getOrCreate($data->bomID);
		$component = $bmm->components->getOrCreate($data->bomID, $data->component);
		self::initHooks();
		$page->js .= self::pw('config')->twig->render('mpm/bmm/component/js.twig', ['bmm' => $bmm]);
		$html = self::displayComponent($data, $bom, $component);
		$bmm::deleteResponse();
		return $html;
	}

	private static function list($data) {
		$fields = ['itemID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mpm\Bom\Header();

		$page->headline = "Bill-of-Material Master";

		if ($filter->exists($data->q)) {
			self::pw('session')->redirect(self::itmUrl($data->q), $http301 = false);
		}

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "BMM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('mpm/bmm/list/.js.twig');
		$html = self::displayList($data, $items);
		self::getBmm()::deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function bmmUrl($itemID = '') {
		if (empty($itemID)) {
			return Menu::bmmUrl();
		}
		return self::bmmFocusUrl($itemID);
	}

	public static function bmmFocusUrl($focus) {
		$filter = new Filters\Mpm\Bom\Header();
		if ($filter->exists($focus) === false) {
			return Menu::bmmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position);

		$url = new Purl(Menu::bmmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'bmm', $pagenbr);
		return $url->getUrl();
	}

	public static function bomUrl($itemID) {
		$url = new Purl(Menu::bmmUrl());
		if ($itemID) {
			$url->query->set('bomID', $itemID);
		}
		return $url->getUrl();
	}

	public static function bomFocusUrl($itemID, $focus) {
		$url = new Purl(self::bomUrl($itemID));
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function bomComponentUrl($itemID, $componentID = '') {
		$url = new Purl(self::bomUrl($itemID));
		if ($componentID) {
			$url->query->set('component', $componentID);
		}
		return $url->getUrl();
	}

	public static function bomComponentDeleteUrl($itemID, $componentID) {
		$url = new Purl(self::bomComponentUrl($itemID, $componentID));
		$url->query->set('action', 'delete-component');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $items) {
		$config = self::pw('config');

		$html  = '';
		$html .= $config->twig->render('mpm/bmm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= self::displayLock($data);
		$html .= $config->twig->render('mpm/bmm/list/list.twig', ['items' => $items, 'bmm' => self::getBmm()]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $html;
	}

	private static function displayBom($data, BomItem $bom) {
		$config  = self::pw('config');
		$html =  '';
		$html .= $config->twig->render('mpm/bmm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= self::displayLock($data);
		$html .= $config->twig->render('mpm/bmm/bom/display.twig', ['bmm' => self::getBmm(), 'bomItem' => $bom]);
		return $html;
	}

	private static function displayComponent($data, BomItem $bom, BomComponent $component) {
		$config  = self::pw('config');
		$html =  '';
		$html .= $config->twig->render('mpm/bmm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= self::displayLock($data);
		$html .= $config->twig->render('mpm/bmm/component/display.twig', ['bmm' => self::getBmm(), 'bomItem' => $bom, 'component' => $component]);
		return $html;
	}

	public static function displayLock($data) {
		$fields = ['bomID|text'];
		self::sanitizeParametersShort($data, $fields);
		$bmm = self::getBmm();

		if ($bmm->header->exists($data->bomID) === false) {
			return '';
		}
		if ($bmm->recordlocker->isLocked($data->bomID) === false) {
			return '';
		}
		if ($bmm->recordlocker->userhasLocked($data->bomID)) {
			return '';
		}
		$msg = "BoM Item $data->bomID is being locked by " . $bmm->recordlocker->getLockingUser($data->bomID);
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "BoM Item $data->bomID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
	}

	public static function displayResponse($data) {
		$bmm = self::getBmm();
		$response = $bmm::getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert-new.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::bmmUrl', function($event) {
			$event->return = self::bmmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::bomUrl', function($event) {
			$event->return = self::bomUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::bomComponentUrl', function($event) {
			$event->return = self::bomComponentUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=mpm)::bomComponentDeleteUrl', function($event) {
			$event->return = self::bomComponentDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=mpm)::bomComponentExitUrl', function($event) {
			$event->return = self::bomFocusUrl($event->arguments(0), $event->arguments(1));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getBmm() {
		if (empty(self::$bmm)) {
			self::$bmm = new BmmManager();
		}
		return self::$bmm;
	}

	public static function lock($bomID) {
		$bmm = self::getBmm();
		return $bmm->lockrecord($bomID);
	}
}
