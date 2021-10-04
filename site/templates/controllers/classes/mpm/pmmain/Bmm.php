<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
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
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		// if (empty($data->action) === false) {
		// 	return self::handleCRUD($data);
		// }
		//
		// if (empty($data->itemID) === false) {
		// 	return self::itm($data);
		// }
		// return self::list($data);
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
	//
	// private static function itm($data) {
	// 	$page   = self::pw('page');
	// 	$validate = new MinValidator();
	//
	// 	if ($data->itemID === 'new') {
	// 		$page->headline = 'ITM: Creating new Item';
	// 	}
	//
	// 	if ($validate->itemid($data->itemID)) {
	// 		$page->headline = "ITM: $data->itemID";
	// 	}
	//
	// 	if ($validate->itemid($data->itemID) === false && $data->itemID != 'new') {
	// 		return self::list($data);
	// 	}
	// 	$item = self::getItm()->getCreateItem($data->itemID);
	// 	$page->js .= self::pw('config')->twig->render("items/itm/js.twig", ['item' => $item, 'itm' => self::getItm()]);
	//
	// 	return self::displayItem($data, $item);
	// }
	//
	// private static function list($data) {
	// 	$fields = ['itemID|text', 'q|text'];
	// 	$data   = self::sanitizeParametersShort($data, $fields);
	// 	$page     = self::pw('page');
	// 	$validate = new MinValidator();
	//
	// 	if ($validate->itemid($data->q)) {
	// 		self::pw('session')->redirect(self::itmUrl($data->q), $http301 = false);
	// 	}
	//
	// 	$filter = new ItemMasterFilter();
	// 	if (empty($data->q) === false) {
	// 		$filter->search($data->q);
	// 		self::pw('page')->headline = "ITM: Searching for '$data->q'";
	// 	}
	//
	// 	$filter->sortby($page);
	// 	$items = $filter->query->paginate(self::pw('input')->pageNum, 10);
	//
	// 	$page->js = self::pw('config')->twig->render('items/item-list.js.twig');
	// 	return self::displayList($data, $items);
	// }

/* =============================================================
	URLs
============================================================= */
	// public static function itmUrl($itemID = '') {
	// 	$url = new Purl(self::pw('pages')->get('pw_template=itm')->url);
	// 	if ($itemID) {
	// 		$url->query->set('itemID', $itemID);
	// 	}
	// 	return $url->getUrl();
	// }
	//
	// public static function itmDeleteUrl($itemID) {
	// 	$url = new Purl(self::itmUrl($itemID));
	// 	$url->query->set('action', 'delete-itm');
	// 	return $url->getUrl();
	// }

/* =============================================================
	Displays
============================================================= */
	// private static function displayList($data, PropelModelPager $items) {
	// 	$config     = self::pw('config');
	// 	$validate   = new MinValidator();
	// 	$htmlwriter = self::pw('modules')->get('HtmlWriter');
	// 	$html   = $config->twig->render('items/itm/bread-crumbs.twig');
	//
	// 	if (self::pw('session')->getFor('response', 'itm')) {
	// 		$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => self::pw('session')->getFor('response', 'itm')]);
	// 	}
	// 	if (empty($data->itemID) === false && $validate->itemid($data->itemID) === false) {
	// 		$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID '$data->itemID' not found in the Item Master"]);
	// 		$html .= $htmlwriter->div('class=mb-3');
	// 	}
	// 	$html  .= $config->twig->render('items/itm/itm/search.twig', ['items' => $items, 'itm' => self::getItm()]);
	// 	$html  .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
	// 	return $html;
	// }
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

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		// $m->addHook('Page(pw_template=itm)::itmDeleteUrl', function($event) {
		// 	$event->return = self::itmDeleteUrl($event->arguments(0));
		// });
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
