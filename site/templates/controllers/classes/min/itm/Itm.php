<?php namespace Controllers\Min\Itm;
// External Libraries, classes
Use Purl\Url as Purl;
// Dplus Models
use ItemMasterItem;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel, ProcessWire\User;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;

class Itm extends Base {

	const SUBFUNCTIONS = [
		'costing'      => ['title' => 'Costing', 'permission' => 'costing'],
		'pricing'      => ['title' => 'Pricing', 'permission' => 'pricing'],
		'warehouses'   => ['title' => 'Warehouses', 'permission' => 'whse'],
		'misc'         => ['title' => 'Misc', 'permission' => 'misc'],
		'xrefs'        => ['title' => 'X-Refs', 'permission' => 'xrefs'],
		// 'dimensions'   => ['title' => 'Packaging', 'permission' => ''],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->itemID) === false) {
			return self::itm($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$input = self::pw('input');

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect($input->url(), $http301 = false);
		}

		$fields = ['itemID|text', 'action|text'];
		$data  = self::sanitizeParametersShort($data, $fields);
		$url   = new Purl($input->url($withQueryString = true));
		$url->query->set('itemID', $data->itemID);
		$url->query->remove('action');

		if ($data->action) {
			$itm  = self::getItm();
			$itm->process_input($input);

			if ($data->action == 'delete-itm') {
				$response = self::pw('session')->getFor('response', 'itm');
				if ($response->has_success()) {
					$url->query->remove('itemID');
				}
			}
		}
		self::pw('session')->redirect($url->getUrl(), $http301 = false);
	}

	private static function itm($data) {
		$page   = self::pw('page');
		$validate = new MinValidator();

		if ($data->itemID === 'new') {
			$page->headline = 'ITM: Creating new Item';
		}

		if ($validate->itemid($data->itemID)) {
			$page->headline = "ITM: $data->itemID";
		}

		if ($validate->itemid($data->itemID) === false && $data->itemID != 'new') {
			return self::list($data);
		}
		$item = self::getItm()->getCreateItem($data->itemID);
		$page->js .= self::pw('config')->twig->render("items/itm/js.twig", ['item' => $item, 'itm' => self::getItm()]);

		return self::displayItem($data, $item);
	}

	private static function list($data) {
		$fields = ['itemID|text', 'q|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$page     = self::pw('page');
		$validate = new MinValidator();

		if ($validate->itemid($data->q)) {
			self::pw('session')->redirect(self::itmUrl($data->q), $http301 = false);
		}

		$filter = new ItemMasterFilter();
		if (empty($data->q) === false) {
			$filter->search($data->q);
			self::pw('page')->headline = "ITM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$page->js = self::pw('config')->twig->render('items/item-list.js.twig');
		return self::displayList($data, $items);
	}

/* =============================================================
	URLs
============================================================= */
	public static function itmUrl($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=itm')->url);
		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

	public static function itmDeleteUrl($itemID) {
		$url = new Purl(self::itmUrl($itemID));
		$url->query->set('action', 'delete-itm');
		return $url->getUrl();
	}


/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $items) {
		$config     = self::pw('config');
		$validate   = new MinValidator();
		$htmlwriter = self::pw('modules')->get('HtmlWriter');
		$html   = $config->twig->render('items/itm/bread-crumbs.twig');

		if (self::pw('session')->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => self::pw('session')->getFor('response', 'itm')]);
		}
		if (empty($data->itemID) === false && $validate->itemid($data->itemID) === false) {
			$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID '$data->itemID' not found in the Item Master"]);
			$html .= $htmlwriter->div('class=mb-3');
		}
		$html  .= $config->twig->render('items/itm/itm/search.twig', ['items' => $items, 'itm' => self::getItm()]);
		$html  .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $html;
	}

	private static function displayItem($data, ItemMasterItem $item) {
		$session = self::pw('session');
		$config  = self::pw('config');
		$itm     = self::getItm();
		$html =  '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');

		if ($itm->getResponse()) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $itm->getResponse()]);
		}

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}

		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/form/display.twig', ['item' => $item, 'itm' => $itm, 'qnotes' => self::pw('modules')->get('QnotesItem')]);
		if ($item->isNew() === false && $itm->recordlocker->userHasLocked($data->itemID)) {
			$html .= self::displayQnotes($data);
		}
		return $html;
	}

	private static function displayQnotes($data) {
		$fields = ['itemID|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$qnotes = self::pw('modules')->get('QnotesItem');
		$config = self::pw('config');
		$item   = self::getItm()->item($data->itemID);
		$html   = $config->twig->render('items/itm/notes/notes.twig', ['item' => $item, 'qnotes' => $qnotes]);
		self::pw('page')->js .= $config->twig->render("items/itm/notes/js.twig", ['item' => $item, 'qnotes' => $qnotes]);
		self::pw('session')->remove('qnotes_itm');
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		parent::initHooks();

		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=itm)::itmDeleteUrl', function($event) {
			$event->return = self::itmDeleteUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::itmAddUrl', function($event) {
			$event->return = self::itmUrl('new');
		});

		$m->addHook('Page(pw_template=itm)::subfunctions', function($event) {
			$user = self::pw('user');
			$event->return = self::getUserSubfunctions($user);
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getUserSubfunctions(User $user = null) {
		$user = $user ? $user : self::pw('user');
		$allowed = [];
		$itmp = self::getItmp();

		foreach (self::SUBFUNCTIONS as $option => $function) {
			if ($itmp->allowUser($user, $function['permission'])) {
				$allowed[$option] = $function['title'];
			}
		}
		return $allowed;
	}

	protected static function validateUserPermission() {
		return self::pw('user')->has_function('itm');
	}
}
