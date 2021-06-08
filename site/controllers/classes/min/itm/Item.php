<?php namespace Controllers\Min\Itm;
// External Libraries, classes
Use Purl\Url as Purl;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Item extends ItmFunction {

	protected static function validateUserPermission() {
		$wire = self::pw();
		$user = $wire->wire('user');
		$page = $wire->wire('page');
		return $user->has_function('itm');
	}

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		$page->show_breadcrumbs = false;

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
		if (self::validateItemidAndPermission($data) === false) {
			self::pw('session')->redirect($input->url());
		}
		$fields = ['itemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$url   = new Purl($input->url($withQueryString = true));
		$url->query->set('itemID', $data->itemID);
		$url->query->remove('action');

		if ($data->action) {
			$itm  = self::getItm();
			$itm->process_input($input);
			if ($data->action == 'delete-itm-item') {
				$url->query->remove('itemID');
			}
		}
		self::pw('session')->redirect($url->getUrl(), $http301 = false);
	}

	public static function itm($data) {
		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect($input->url());
		}
		$fields = ['itemID|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$page    = self::pw('page');
		$config  = self::pw('config');
		$session = self::pw('session');
		$itm     = self::getItm();
		$validate = new MinValidator();
		$htmlwriter   = self::pw('modules')->get('HtmlWriter');
		$html = '';

		$html .= $config->twig->render('items/itm/bread-crumbs.twig');

		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}

		if ($data->itemID === 'new') {
			$page->headline = 'ITM: Creating new Item';
		}

		if ($validate->itemid($data->itemID)) {
			$page->headline = "ITM: $data->itemID";
		}

		if ($validate->itemid($data->itemID) === false && $data->itemID != 'new') {
			$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID '$data->itemID' not found in the Item Master"]);
			$html .= $htmlwriter->div('class=mb-3');
			$html .= self::list($data);
			return $html;
		}
		$page->customerlookupURL = self::pw('pages')->get('pw_template=mci-lookup')->url;
		$item = $itm->get_item($data->itemID);
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/form/display.twig', ['item' => $item, 'itm' => $itm, 'recordlocker' => $itm->recordlocker]);
		$page->js   .= $config->twig->render("items/itm/js.twig", ['item' => $item, 'itm' => $itm]);
		if ($item->isNew() === false) {
			$html .= self::qnotes($data);
		}
		return $html;
	}

	public static function qnotes($data) {
		$fields = ['itemID|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$qnotes = self::pw('modules')->get('QnotesItem');
		$config = self::pw('config');
		$user   = self::pw('user');
		$item   = self::getItm()->get_item($data->itemID);
		$html   = $config->twig->render('items/itm/notes/notes.twig', ['item' => $item, 'qnotes' => $qnotes]);
		self::pw('page')->js .= $config->twig->render("items/itm/notes/js.twig", ['item' => $item, 'qnotes' => $qnotes]);
		self::pw('session')->remove('qnotes_itm');
		return $html;
	}

	public static function list($data) {
		$fields = ['itemID|text', 'q|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$input    = self::pw('input');
		$page     = self::pw('page');
		$config   = self::pw('config');
		$validate = new MinValidator();

		if ($validate->itemid($data->q)) {
			self::pw('session')->redirect(self::itmUrl($data->q), $http301 = false);
		}

		$filter = new ItemMasterFilter();
		$filter->search($data->q);
		$filter->sortby($page);
		$items = $filter->query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$html  = $config->twig->render('items/itm/bread-crumbs.twig');
		$html  .= $config->twig->render('items/itm/itm/search.twig', ['items' => $items, 'itm' => self::getItm()]);
		$html  .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		$page->js = $config->twig->render('items/item-list.js.twig');
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function itmDeleteUrl($itemID) {
		$url = new Purl(self::itmUrl($itemID));
		$url->query->set('action', 'delete-itm');
		return $url->getUrl();
	}

	public static function initHooks() {
		parent::initHooks();
		$m = self::pw('modules')->get('Itm');


		$m->addHook('Page(pw_template=itm)::itmDeleteUrl', function($event) {
			$event->return = self::itmUrl($event->arguments(0));
		});
	}
}
