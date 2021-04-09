<?php namespace Controllers\Mii\Ii;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemPricingQuery, ItemPricing;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\CiLoadCustomerShipto;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster  as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\IiFunction;

class Item extends IiFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		if (empty($data->itemID) === false) {
			return self::item($data);
		}
		return self::list($data);
	}

	public static function item($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
		self::sanitizeParametersShort($data, ['itemID|text']);
		$html = '';

		$page    = self::pw('page');
		$config  = self::pw('config');
		$pages   = self::pw('pages');
		$modules = self::pw('modules');
		$htmlwriter = $modules->get('HtmlWriter');
		$jsonM      = $modules->get('JsonDataFiles');

		$item = ItemMasterItemQuery::create()->findOneByItemid($data->itemID);
		$itempricing = ItemPricingQuery::create()->findOneByItemid($data->itemID);
		$page->headline = "II: $data->itemID";
		$toolbar = $config->twig->render('items/ii/toolbar.twig', ['item' => $item]);
		$links   = $config->twig->render('items/ii/item/ii-links.twig', ['itemID' => $data->itemID, 'lastmodified' => $jsonM->file_modified(session_id(), 'ii-stock'), 'refreshurl' => $page->get_itemURL($data->itemID)]);
		$details = $config->twig->render('items/ii/item/item-data.twig', ['item' => $item, 'itempricing' => $itempricing]);
		$stock   = self::itemStock($data->itemID);
		$header  = self::itemHeader($data->itemID);

		$html .= self::breadCrumbs();
		$html .= "<div class='row'>";
			$html .= $htmlwriter->div('class=col-sm-2 pl-0', $toolbar);
			$html .= $htmlwriter->div('class=col-sm-10', $links.$header.$details.$stock);
		$html .= "</div>";
		return $html;
	}

	private static function itemStock($itemID) {
		$jsonInfo = [
			'code'      => 'ii-stock',
			'formatter' => 'ii:stock',
			'twig'      => 'items/ii/item/stock.twig',
		];
		return self::jsonFormattedData($jsonInfo, $itemID);
	}

	private static function itemHeader($itemID) {
		$jsonInfo = [
			'code'      => 'ii-item',
			'formatter' => 'ii:item',
			'twig'      => 'items/ii/item/item.twig',
		];
		return self::jsonFormattedData($jsonInfo, $itemID);
	}

	private static function jsonFormattedData($jsonInfo, $itemID) {
		$htmlwriter = '';
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$session = self::pw('session');
		$formatters = $modules->get('ScreenFormatters');
		$jsonM      = $modules->get('JsonDataFiles');

		$json = $jsonM->get_file(session_id(), $jsonInfo['code']);

		if ($json['itemid'] != $itemID) {
			self::requestIiItem($itemID);
			$session->redirect(self::pw('page')->fullURL->getUrl(), $http301 = false);
		}

		if ($jsonM->file_exists(session_id(), $jsonInfo['code'])) {
			$session->setFor('ii', 'item', 0);

			if ($json['error']) {
				$htmlwriter .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$formatter = $formatters->formatter($jsonInfo['formatter']);
				$formatter->init_formatter();
				$htmlwriter .= $config->twig->render($jsonInfo['twig'], ['itemID' => $itemID, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint()]);
			}
		} else {
			if ($session->getFor('ii', 'item') > 3) {
				$htmlwriter .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "JSON Decode Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $jsonM->get_error()]);
			} else {
				$session->setFor('ii', 'item', $session->getFor('ii', 'item') + 1);
				self::requestIiItem($itemID);
				$session->redirect(self::pw('page')->fullURL->getUrl(), $http301 = false);
			}
		}
		return $htmlwriter;
	}

	public static function list($data) {
		if (self::validateUserPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
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
}
