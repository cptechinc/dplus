<?php namespace Controllers\Wm;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dpluso Model
use BininfoQuery, Bininfo;
use WhsesessionQuery, Whsesession;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, Processwire\SearchInventory, Processwire\WarehouseManagement;
// Dplus Classes
use Dplus\Wm\Binr as BinrCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Binr extends AbstractController {
	/** @var SearchInventory **/
	private static $inventory;

	/** @var WarehouseManagement **/
	private static $whsem;

	/** @var Warehouse **/
	private static $warehouse;

	/** @var Whsesession **/
	private static $whsesession;

	public static function index($data) {
		$fields = ['scan|text', 'action|text', 'frombin|text', 'tobin|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->scan) === false) {
			return self::handleScan($data);
		}

		if (empty($data->serialnbr) === false || empty($data->lotnbr) === false || empty($data->itemID) === false) {
			return self::handleItem($data);
		}

		return self::searchItemForm($data);
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'scan|text']);

		switch ($data->action) {
			case 'inventory-search':
				$whsem = self::getWarehouseManagement();
				$whsem->requestInventorySearch(strtoupper($data->scan));
				$url = new Purl(self::pw('page')->url);
				$url->query->set('scan', $data->scan);
				self::pw('session')->redirect($url->getUrl(), $http301 = false);
				break;
			case 'bin-reassign';
				$data = self::sanitizeParametersShort($data, ['itemID|text', 'frombin|text', 'tobin|text', 'serialnbr|text', 'lotnbr|text', 'qty|float']);
				$binr = new BinrCRUD();
				$binr->requestBinReassignment($data);
				$params = explode('&', self::pw('input')->queryString());
				$url = new Purl(self::pw('page')->url);
				if (array_key_exists('frombin', $params)) {
					$url->query->set('frombin', $params['frombin']);
				}
				if (array_key_exists('tobin', $params)) {
					$url->query->set('tobin', $params['tobin']);
				}
				self::pw('session')->redirect($url->getUrl(), $http301 = false);
				break;
		}
	}

	public static function handleScan($data) {
		$fields = ['scan|text', 'action|text', 'frombin|text', 'tobin|text', 'binID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		$html = '';
		$config = self::pw('config');
		$modules = self::pw('modules');
		$config->binr = $modules->get('ConfigsBinr');
		/** @var SearchInventory **/
		$inventory = self::getInventorySearch();
		$resultscount = $inventory->query()->count();

		if ($resultscount === 0) {
			return $config->twig->render('warehouse/binr/inventory-results.twig', ['items' => []]);
		}

		if ($resultscount === 1) {
			$item = $inventory->query()->findOne();
			$url = self::pw('page')->binr_itemURL($item);
			self::pw('session')->redirect($url, $http301 = false);
		}

		// Multiple Items - count the number Distinct Item IDs
		$countItemids = $inventory->count_itemids_distinct($data->binID);

		if ($countItemids === 1) {
			return self::handleScanInventorySingleItemid($data);
		}
		$items = $inventory->get_items_distinct($data->binID);
		return $config->twig->render('warehouse/binr/inventory-results.twig', ['resultscount' => $resultscount, 'items' => $items]);
	}

	private static function handleScanInventorySingleItemid($data) {
		$fields    = ['scan|text', 'action|text', 'frombin|text', 'tobin|text', 'binID|text'];
		$data      = self::sanitizeParametersShort($data, $fields);
		$inventory = self::getInventorySearch();
		$config    = self::pw('config');

		$q = $inventory->query();
		if ($data->binID) {
			$q->filterByBin($data->binID);
		}
		$item = $q->findOne();

		if ($item->is_lotted() === false && $item->is_serialized() === false) {
			$whsem = self::getWarehouseManagement();
			$whsem->requestItemBins($item->itemid);
			$url = new Purl(self::pw('page')->url);
			$url->query->set('itemID', $item->itemid);
			self::pw('session')->redirect($url->getUrl(), $http301 = false);
		}

		// If Item is Lotted / Serialized show results to choose which lot or serial to move
		$countLotserial = $inventory->count_itemid_records($item->itemid, $data->binID);
		$items = $inventory->get_items_distinct($data->binID);
		$warehouse = self::getCurrentUserWarehouse();

		if ($config->twigloader->exists("warehouse/binr/$config->company/inventory-results.twig")) {
			return $config->twig->render("warehouse/binr/$config->company/inventory-results.twig", ['config' => $config->binr, 'resultscount' => $countLotserial, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
		}
		return $config->twig->render('warehouse/binr/inventory-results.twig', ['config' => $config->binr, 'resultscount' => $countLotserial, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
	}

	private static function handleItem($data) {
		$fields = ['lotnbr|text', 'serialnbr|text', 'itemID|text', 'binID|text'];
		$data   = self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$config = self::pw('config');
		$warehouse   = self::getCurrentUserWarehouse();
		$inventory   = self::getInventorySearch();
		$whsesession = self::getWhseSession();

		if ($data->lotnbr) {
			$page->scan   = $data->lotnbr;
			$resultscount = $inventory->count_lotserial_records($data->lotnbr, $data->binID);
		}
		if ($data->serialnbr) {
			$page->scan   = $data->serialnbr;
			$resultscount = $inventory->count_lotserial_records($data->serialnbr, $data->binID);
		}
		if ($data->itemID) {
			$page->scan   = $data->itemID;
			$resultscount = $inventory->count_lotserial_records($data->itemID, $data->binID);
		}

		if ($resultscount > 1) {
			$items = $inventory->get_items_distinct();
			return $config->twig->render('warehouse/binr/inventory-results.twig', ['resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory]);
		}

		$html = '';

		if (empty(self::pw('session')->get('binr')) === false) {
			$html .= $config->twig->render('warehouse/binr/binr-result.twig', ['whsesession' => $whsesession, 'nexturl' => $page->url]);
			self::pw('session')->remove('binr');
			return $html;
		}

		// $resultscount == 1

		// Prepare Binr Form
		$item = $data->lotnbr || $data->serialnbr ? $inventory->get_lotserial($page->scan, $data->binID) : $inventory->get_invsearch_by_itemid($page->scan, $data->binID);
		$currentbins = BininfoQuery::create()->filterByItem(session_id(), $item)->select_bin_qty()->find();
		// 1. Binr form
		$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
		$html .= $config->twig->render('warehouse/binr/binr-form.twig', ['config' => $config->binr, 'whsesession' => $whsesession, 'item' => $item, 'inventory' => $inventory]);
		// 2. Choose From Bin Modal
		$html .= $config->twig->render('warehouse/binr/from-bins-modal.twig', ['config' => $config->binr, 'item' => $item, 'bins' => $currentbins]);
		// 3. Choose To Bin Modals
		$html .= $config->twig->render('warehouse/binr/to-bins-modal.twig', ['config' => $config->binr, 'currentbins' => $currentbins, 'warehouse' => $warehouse, 'item' => $item, 'inventory' => $inventory]);
		// 4. Warehouse Config JS
		$bins = $warehouse->get_bins();
		$availablebins = BininfoQuery::create()->filterBySessionItemid(session_id(), $item->itemID)->find()->toArray('Bin');
		$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
		$html .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig, 'validfrombins' => $availablebins)]);
		return $html;
	}

	private static function searchItemForm($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->formurl = $page->url;
		return $config->twig->render('warehouse/item-form.twig');
	}

	public static function getWarehouseManagement() {
		if (empty(self::$whsem)) {
			self::$whsem = self::pw('modules')->get('WarehouseManagement');
		}
		return self::$whsem;
	}

	public static function getInventorySearch() {
		if (empty(self::$inventory)) {
			self::$inventory = self::pw('modules')->get('SearchInventory');
		}
		return self::$inventory;
	}

	public static function getCurrentUserWarehouse() {
		if (empty(self::$warehouse)) {
			self::$warehouse = WarehouseQuery::create()->findOneById(self::pw('user')->whseid);
		}
		return self::$warehouse;
	}

	public static function getWhseSession() {
		if (empty(self::$whsesession)) {
			self::$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
		}
		return self::$whsesession;
	}

	public static function init() {
		$wm = self::pw('modules')->get('WarehouseManagement');
		$wm->addHook('Page::binr_itemURL', function($event) {
			$p = $event->object;
			$item = $event->arguments(0);
			$url = new Purl($p->parent('template=warehouse-menu')->child('template=redir')->url);
			$url->query->set('action','search-item-bins');
			$url->query->set('itemID', $item->itemid);
			$url->query->set($item->get_itemtypeproperty(), $item->get_itemidentifier());
			$url->query->set('binID', $item->bin);
			$url->query->set('page', $p->fullURL->getUrl());
			$event->return = $url->getUrl();
		});

		$wm->addHookProperty('Page::scan', function($event) {
			$p = $event->object;
			$event->return = !empty($p->scan) ? $p->scan : false;
		});

		$wm->addHookProperty('Page::tobin', function($event) {
			$p = $event->object;
			$event->return = !empty($p->tobin) ? $p->tobin : '';
		});

		$wm->addHookProperty('Page::frombin', function($event) {
			$p = $event->object;
			$event->return = !empty($p->frombin) ? $p->frombin : '';
		});
	}
}
