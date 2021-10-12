<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use BomItem;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Mpm\Pmmain\Bmm as BmmManager;
// Mvc Controllers
use Controllers\Mpm\Base;

class Bmm extends Base {
	private static $bmm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['bomID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		// if (empty($data->action) === false) {
		// 	return self::handleCRUD($data);
		// }

		if (empty($data->bomID) === false) {
			return self::bom($data);
		}
		return self::list($data);
	}

	// public static function handleCRUD($data) {
	// 	$input = self::pw('input');
	//
	// 	if (self::validateUserPermission() === false) {
	// 		self::pw('session')->redirect($input->url(), $http301 = false);
	// 	}
	//
	// 	$fields = ['itemID|text', 'action|text'];
	// 	$data  = self::sanitizeParametersShort($data, $fields);
	// 	$url   = new Purl($input->url($withQueryString = true));
	// 	$url->query->set('itemID', $data->itemID);
	// 	$url->query->remove('action');
	//
	// 	if ($data->action) {
	// 		$itm  = self::getItm();
	// 		$itm->process_input($input);
	//
	// 		if ($data->action == 'delete-itm') {
	// 			$response = self::pw('session')->getFor('response', 'itm');
	// 			if ($response->has_success()) {
	// 				$url->query->remove('itemID');
	// 			}
	// 		}
	// 	}
	// 	self::pw('session')->redirect($url->getUrl(), $http301 = false);
	// }

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
		return self::displayBom($data, $bom);
	}

	private static function list($data) {
		$fields = ['itemID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page     = self::pw('page');
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

		// $page->js = self::pw('config')->twig->render('items/item-list.js.twig');
		return self::displayList($data, $items);
	}

/* =============================================================
	URLs
============================================================= */
	public static function bmmUrl($itemID = '') {
		$url = new Purl(Menu::bmmUrl());
		if ($itemID) {
			$url->query->set('bomID', $itemID);
		}
		return $url->getUrl();
	}

	public static function bmmComponentUrl($itemID, $componentID = '') {
		$url = new Purl(self::bmmUrl($itemID));
		if ($componentID) {
			$url->query->set('component', $componentID);
		}
		return $url->getUrl();
	}
	public static function bmmComponentDeleteUrl($itemID, $componentID) {
		$url = new Purl(self::bmmComponentUrl($itemID, $componentID));
		$url->query->set('action', 'delete-component');
		return $url->getUrl();
	}



	// public static function itmDeleteUrl($itemID) {
	// 	$url = new Purl(self::itmUrl($itemID));
	// 	$url->query->set('action', 'delete-itm');
	// 	return $url->getUrl();
	// }

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $items) {
		$config     = self::pw('config');

		$html   = '';
		$html  .= $config->twig->render('mpm/bmm/list/list.twig', ['items' => $items, 'bmm' => self::getBmm()]);
		$html  .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $html;
	}

	private static function displayBom($data, BomItem $bom) {
		$config  = self::pw('config');
		$html =  '';

		$html .= $config->twig->render('mpm/bmm/bom/display.twig', ['bmm' => self::getBmm(), 'bomItem' => $bom]);
		return $html;
	}

	//
	// private static function displayItem($data, ItemMasterItem $item) {
	// 	$session = self::pw('session');
	// 	$config  = self::pw('config');
	// 	$itm     = self::getItm();
	// 	$html =  '';
	// 	$html .= $config->twig->render('items/itm/bread-crumbs.twig');
	//
	// 	if ($itm->getResponse()) {
	// 		$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $itm->getResponse()]);
	// 	}
	//
	// 	if ($session->response_qnote) {
	// 		$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
	// 		$session->remove('response_qnote');
	// 	}
	//
	// 	$html .= self::lockItem($data->itemID);
	// 	$html .= $config->twig->render('items/itm/itm-links.twig');
	// 	$html .= $config->twig->render('items/itm/form/display.twig', ['item' => $item, 'itm' => $itm, 'qnotes' => self::pw('modules')->get('QnotesItem')]);
	// 	if ($item->isNew() === false && $itm->recordlocker->userHasLocked($data->itemID)) {
	// 		$html .= self::displayQnotes($data);
	// 	}
	// 	$itm->deleteResponse();
	// 	return $html;
	// }
	//
	// private static function displayQnotes($data) {
	// 	$fields = ['itemID|text'];
	// 	$data   = self::sanitizeParametersShort($data, $fields);
	// 	$qnotes = self::pw('modules')->get('QnotesItem');
	// 	$config = self::pw('config');
	// 	$item   = self::getItm()->item($data->itemID);
	// 	$html   = $config->twig->render('items/itm/notes/notes.twig', ['item' => $item, 'qnotes' => $qnotes]);
	// 	self::pw('page')->js .= $config->twig->render("items/itm/notes/js.twig", ['item' => $item, 'qnotes' => $qnotes]);
	// 	self::pw('session')->remove('qnotes_itm');
	// 	return $html;
	// }
	//

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

		$m->addHook('Page(pw_template=mpm)::bomUrl', function($event) {
			$event->return = self::bmmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=mpm)::bomComponentUrl', function($event) {
			$event->return = self::bmmComponentUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=mpm)::bomComponentDeleteUrl', function($event) {
			$event->return = self::bmmComponentDeleteUrl($event->arguments(0), $event->arguments(1));
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
