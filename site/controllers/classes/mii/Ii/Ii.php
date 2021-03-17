<?php namespace Controllers\Mii;
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

class Ii extends AbstractController {
	public static function index($data) {
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (empty($data->itemID) === false) {
			return self::item($data);
		}
		return self::list($data);
	}

	public static function item($data) {
		self::pw('modules')->get('DpagesMii')->init_iipage();
		$fields = ['custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MinValidator();

		if ($validate->itemid($data->itemID) === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $data->itemID could not be found"]);
			return $page->body;
		}

		$page   = self::pw('page');
		$config = self::pw('config');
		$pages   = self::pw('pages');
		$modules = self::pw('modules');
		$html    = $modules->get('HtmlWriter');
		$jsonM   = $modules->get('JsonDataFiles');


		$item = ItemMasterItemQuery::create()->findOneByItemid($data->itemID);
		$itempricing = ItemPricingQuery::create()->findOneByItemid($data->itemID);
		$page->headline = "II: $data->itemID";
		$toolbar = $config->twig->render('items/ii/toolbar.twig', ['item' => $item]);
		$links   = $config->twig->render('items/ii/item/ii-links.twig', ['itemID' => $data->itemID, 'lastmodified' => $jsonM->file_modified(session_id(), 'ii-stock'), 'refreshurl' => $page->get_itemURL($data->itemID)]);
		$details = $config->twig->render('items/ii/item/item-data.twig', ['item' => $item, 'itempricing' => $itempricing]);
		$stock   = self::itemStock($data->itemID);
		$header  = self::itemHeader($data->itemID);

		$page->body .= "<div class='row'>";
			$page->body .= $html->div('class=col-sm-2 pl-0', $toolbar);
			$page->body .= $html->div('class=col-sm-10', $links.$header.$details.$stock);
		$page->body .= "</div>";
		return $page->body;
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
		$html = '';
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$session = self::pw('session');
		$formatters = $modules->get('ScreenFormatters');
		$jsonM      = $modules->get('JsonDataFiles');
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
				$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$formatter = $formatters->formatter($jsonInfo['formatter']);
				$formatter->init_formatter();
				$html .= $config->twig->render($jsonInfo['twig'], ['itemID' => $itemID, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint()]);
			}
		} else {
			if ($session->getFor('ii', 'item') > 3) {
				$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "JSON Decode Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $jsonM->get_error()]);
			} else {
				$session->setFor('ii', 'item', $session->getFor('ii', 'item') + 1);
				self::requestIiItem($itemID);
				$session->redirect(self::pw('page')->fullURL->getUrl(), $http301 = false);
			}
		}
		return $html;
	}

	public static function requestIiItem($itemID, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array("DBNAME=$db", 'IISELECT', "ITEMID=$itemID");
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

	public static function list($data) {
		self::pw('modules')->get('DpagesMii')->init_iipage();
		$fields = ['q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
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
		$page->body .= $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => $items, 'pricing' => $pricingM]);
		$page->body .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $page->body;
	}
}
