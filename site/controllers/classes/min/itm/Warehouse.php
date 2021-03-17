<?php namespace Controllers\Min\Itm;
// Dplus Model
use WarehouseInventoryQuery, WarehouseInventory;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\ItmWarehouse as WarehouseCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Warehouse extends ItmFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$page->show_breadcrumbs = false;

		if (empty($data->whseID) === false) {
			return self::warehouse($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$fields = ['itemID|text', 'whseID|text', 'action|text'];
		$data   = self::sanitizeParameters($data, $fields);
		$input  = self::pw('input');
		$itmW  = self::getItmWarehouse();
		$itmW->init_configs();

		if ($data->action) {
			$itmW->process_input($input);
			$data->whseID = $data->action == 'remove-itm-whse' ? '' : $data->whseID;
		}

		self::pw('session')->redirect($page->itm_warehouseURL($data->itemID, $data->whseID), $http301 = false);
	}

	public static function warehouse($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$fields = ['itemID|text', 'whseID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$html = '';
		$config  = self::pw('config');
		$page    = self::pw('page');
		$page->headline = "ITM: $data->itemID Warehouse $data->whseID";
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		$html .= $config->twig->render('items/itm/itm-links.twig', ['page_itm' => $page->parent]);
		$html .= Itm::lockItem($data->itemID);
		$validate = self::getMinValidator();

		if ($validate->whseid($data->whseID) === false) {
			return self::invalidWhse($data);
		}
		$itm  = self::getItm();
		$itmW = self::getItmWarehouse();
		$itmW->init_configs();
		$item = $itm->get_item($data->itemID);
		$whse = $itmW->getOrCreate($data->itemID, $data->whseID);

		if ($whse->isNew()) {
			$page->headline = "ITM: $data->itemID Warehouse Add";
		}

		if (self::pw('session')->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => self::pw('session')->getFor('response', 'itm')]);
		}

		if ($whse->isNew() === false) {
			self::lockItemWarehouse($whse);
		}

		$html .= $config->twig->render('items/itm/warehouse/display.twig', ['item' => $item, 'warehouse' => $whse, 'm_whse' => $itmW, 'recordlocker' => $itmW->recordlocker]);
		$html .= $config->twig->render('items/itm/warehouse/bins-modal.twig', ['itemID' => $data->itemID, 'm_whse' => $itmW]);
		return $html;
	}

	private static function invalidWhse($data) {
		$fields = ['whseID|text'];
		$validate = self::getMinValidator();
		$html = '';
		if ($validate->whseid($data->whseID) === false) {
			$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Warehouse Not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Warehouse '$data->whseID' not found "]);
			$htmlwriter = self::pw('modules')->get('HtmlWriter');
			$url = $page->itm_warehouseURL($data->itemID);
			$html .= $htmlwriter->a("class=btn btn-primary mt-3|href=$url", $htmlwriter->icon('fa fa-undo')." Warehouses");
		}
		return $html;
	}

	private static function lockItemWarehouse(WarehouseInventory $whse) {
		$itmW = self::getItmWarehouse();
		$html = '';
		$itmW->lockrecord($whse);

		if ($itmW->recordlocker->function_locked($whse->itemid) && !$itmW->recordlocker->function_locked_by_user($itmW->lockerkey($whse))) {
			$config = self::pw('config');
			$msg = "Warehouse $whse->whseid for $whse->itemid is being locked by " . $itmW->recordlocker->get_locked_user($itmW->lockerkey($whse));
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $whse->whseid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= '<div class="mb-3"></div>';
		}
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$html = '';
		$config  = self::pw('config');
		$page    = self::pw('page');
		$itm     = self::getItm();
		$itmw    = self::getItmWarehouse();
		$itmw->recordlocker->remove_lock($page->lockcode);
		$page->headline = "ITM: $data->itemID Warehouses";
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		$html .= $config->twig->render('items/itm/itm-links.twig', ['page_itm' => $page->parent]);
		$html .= $config->twig->render('items/itm/warehouse/list-display.twig', ['itmw' => $itmw, 'itemID' => $data->itemID, 'item' => $itm->item($data->itemID), 'warehouses' => $itmw->get_itemwarehouses($data->itemID)]);
		return $html;
	}

	public static function getItmWarehouse() {
		return self::pw('modules')->get('ItmWarehouse');
	}

}
