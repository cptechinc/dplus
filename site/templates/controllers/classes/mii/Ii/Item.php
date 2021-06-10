<?php namespace Controllers\Mii\Ii;
// Purl
use Purl\Url as Purl;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemPricingQuery, ItemPricing;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData, ProcessWire\CiLoadCustomerShipto;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster  as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\IiFunction;

class Item extends IiFunction {
	const SECTIONS = [
		'item' => [
			'code'      => 'ii-item',
			'formatter' => 'ii:item',
			'twig'      => 'items/ii/item/item.twig',
		],
		'stock' => [
			'code'      => 'ii-stock',
			'formatter' => 'ii:stock',
			'twig'      => 'items/ii/item/stock.twig',
		]
	];

/* =============================================================
	1. Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'q|text', 'refresh|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if (empty($data->itemID) === false) {
			if ($data->refresh) {
				self::requestIiItem($data->itemID);
				self::pw('session')->redirect(self::itemUrl($data->itemID), $http301 = false);
			}
			return self::item($data);
		}
		return self::list($data);
	}

	public static function item($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);

		self::getData($data);
		$page = self::pw('page');
		$page->headline = "II: $data->itemID";
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	# Inherited from IiFunction

/* =============================================================
	3. URLs
============================================================= */
	public static function itemUrl($itemID, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->query->set('itemID', $itemID);

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	4. Data Retrieval
============================================================= */
	private static function getData($data) {
		foreach (self::SECTIONS as $key => $jsonInfo) {
			self::getDataSection($data, $jsonInfo);
		}
		return true;
	}

	private static function getDataSection($data, $jsonInfo) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile($jsonInfo['code']);
		$session = self::pw('session');

		if ($jsonm->exists($jsonInfo['code'])) {
			if ($json['itemid'] != $data->itemID) {
				$jsonm->delete($jsonInfo['code']);
				$session->redirect(self::itemUrl($data->itemID, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'item', 0);
			return true;
		}

		if ($session->getFor('ii', 'item') > 3) {
			return false;
		}
		$session->setFor('ii', 'item', ($session->getFor('ii', 'item') + 1));
		$session->redirect(self::itemUrl($data->itemID, $refresh = true), $http301 = false);
	}

/* =============================================================
	5. Displays
============================================================= */
	public static function list($data) {
		if (self::validateUserPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$pricingM = self::pw('modules')->get('ItemPricing');
		$filter = new ItemMasterFilter();
		$filter->sortby($page);

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect($page->url."?itemID=$data->q", $http301 = false);
			}

			$filter->search($data->q);
			$page->headline = "II: Searching for '$data->q'";
		}
		$config = self::pw('config');
		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$pricingM->request_multiple(array_keys($items->toArray(ItemMasterItem::get_aliasproperty('itemid'))));

		$page->searchURL = $page->url;
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/item-search.twig', ['items' => $items, 'pricing' => $pricingM]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $html;
	}

	private static function display($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$jsonM  = self::getJsonModule();

		$html = new WireData();
		foreach (self::SECTIONS as $key => $jsonInfo) {
			$html->$key = self::displaySectionFormatted($data, $jsonInfo);
		}
		$page = self::pw('page');
		$page->lastmodified = $jsonM->lastModified('ii-stock');
		$page->refreshurl   = self::itemUrl($data->itemID, true);
		$item = ItemMasterItemQuery::create()->findOneByItemid($data->itemID);
		$itempricing = ItemPricingQuery::create()->findOneByItemid($data->itemID);
		return $config->twig->render('items/ii/item/display.twig', ['item' => $item, 'itempricing' => $itempricing, 'html' => $html]);
	}

	protected static function displaySectionFormatted($data, $jsonInfo) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile($jsonInfo['code']);

		if ($jsonm->exists($jsonInfo['code']) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$formatters = self::pw('modules')->get('ScreenFormatters');
		$formatter = $formatters->formatter($jsonInfo['formatter']);
		$formatter->init_formatter();
		return $config->twig->render($jsonInfo['twig'], ['itemID' => $data->itemID, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint()]);
	}
}
