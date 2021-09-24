<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemSubstituteQuery, ItemSubstitute;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Min\Inmain\Itm\Substitutes as CRUDManager;
// Controllers
use Controllers\Min\Itm\Xrefs as XRefsController;

class Substitutes extends Base {
	private static $crud;

	public static function index($data) {
		$fields = ['itemID|text', 'subitemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->subitemID) == false) {
			return self::sub($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'subitemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
		if (empty($data->action)) {
			self::pw('session')->redirect(self::subListUrl($data->itemID), $http301 = false);
		}
		$itmSub = self::getItmSubstitutes();
		$itmSub->processInput(self::pw('input'));

		$url = self::subUrl($data->itemID, $data->subitemID);
		switch ($data->action) {
			case 'delete':
				$url = self::subListUrl($data->itemID);
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		self::pw('page')->headline = "ITM: $data->itemID Substitutes";

		$filter = new Filters\Min\ItemSubstitute();
		$filter->itemid($data->itemID);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 1);
		self::pw('page')->js .= self::pw('config')->twig->render('items/itm/xrefs/substitutes/list/js.twig');
		return self::displayList($data, $xrefs);
	}

	private static function sub($data) {
		self::pw('page')->headline = "ITM: $data->itemID Substitute $data->subitemID";

		$itmSub = self::getItmSubstitutes();
		$item   = $itmSub->getItm()->item($data->itemID);
		$sub    = $itmSub->getOrCreate($data->itemID, $data->subitemID);

		if ($sub->isNew()) {
			self::pw('page')->headline = "ITM: $data->itemID Substitute Add";
		}

		if ($sub->isNew() === false) {
			$itmSub->lockrecord($sub);
		}

		self::pw('page')->js .= self::pw('config')->twig->render('items/itm/xrefs/substitutes/sub/js.twig');
		return self::displaySub($data, $item, $sub);
	}


/* =============================================================
	Url Functions
============================================================= */
	public static function subListUrl($itemID, $subitemID = '') {
		$url = new Purl(self::xrefUrlSubstitutes($itemID));
		if ($subitemID) {
			$url->query->set('focus', $subitemID);
		}
		return $url->getUrl();
	}

	public static function subUrl($itemID, $subitemID) {
		$url = new Purl(self::xrefUrlSubstitutes($itemID));
		$url->query->set('subitemID', $subitemID);
		return $url->getUrl();
	}

	public static function subDeleteUrl($itemID, $subitemID) {
		$url = new Purl(self::subUrl($itemID, $subitemID));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		self::initHooks();
		$itm    = self::pw('modules')->get('Itm');
		$itmSub = self::getItmSubstitutes();
		$itmSub->recordlocker->deleteLock();

		$item = $itm->item($data->itemID);
		$html  = self::breadCrumbs();
		$html  = self::displayResponse();
		$html .= self::displaySubstitutes($data, $item, $xrefs);
		$itmSub->deleteResponse();
		return $html;
	}

	private static function displaySubstitutes($data, ItemMasterItem $item, PropelModelPager $xrefs) {
		$itmSub = self::getItmSubstitutes();
		return self::pw('config')->twig->render('items/itm/xrefs/substitutes/list/display.twig', ['item' => $item, 'substitutes' => $xrefs, 'itmSub' => $itmSub]);
	}

	private static function displaySub($data, ItemMasterItem $item, ItemSubstitute $sub) {
		self::initHooks();
		$itmSub = self::getItmSubstitutes();

		$html   = self::breadCrumbs();
		$html  .= '<div class="mb-3">' . self::displayLock($data, $sub) . '</div>';
		$html  .= '<div class="mb-3">' . self::displayResponse() . '</div>';
		$html  .= self::pw('config')->twig->render('items/itm/xrefs/substitutes/sub/display.twig', ['item' => $item, 'sub' => $sub, 'itmSub' => $itmSub]);
		$itmSub->deleteResponse();
		return $html;
	}

	private static function displayLock($data, ItemSubstitute $sub) {
		$itmSub = self::getItmSubstitutes();

		if ($sub->isNew()) {
			return '';
		}
		$itmSub->lockrecord($sub);
		if ($itmSub->recordlocker->isLocked($itmSub->getRecordlockerKey($sub)) && $itmSub->recordlocker->userHasLocked($itmSub->getRecordlockerKey($sub)) === false) {
			$msg = "ITM Item $sub->itemid Substitute $sub->subitemid is being locked by " . $itmSub->recordlocker->getLockingUser($itmSub->getRecordlockerKey($sub));
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Substitute is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
	}

	private static function displayResponse() {
		$itmSub = self::getItmSubstitutes();

		$response = $itmSub->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('items/itm/response-alert-new.twig', ['response' => $response]);
	}


/* =============================================================
	Hook Functions
============================================================= */
	public static function initHooks() {
		XRefsController::initHooks();

		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=itm)::subListUrl', function($event) {
			$event->return = self::subListUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::subUrl', function($event) {
			$event->return = self::subUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::subNewUrl', function($event) {
			$event->return = self::subUrl($event->arguments(0), 'new');
		});

		$m->addHook('Page(pw_template=itm)::subDeleteUrl', function($event) {
			$event->return = self::subDeleteUrl($event->arguments(0), $event->arguments(1));
		});
	}

/* =============================================================
	Supplmental Functions
============================================================= */
	public static function getItmSubstitutes() {
		if (empty(self::$crud)) {
			$crud = new CRUDManager();
			$crud->init();
			self::$crud = $crud;
		}
		return self::$crud;
	}
}
