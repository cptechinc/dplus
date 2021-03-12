<?php namespace Controllers\Min\Itm;

use Controllers\Min\Itm\ItmFunction;
use Controllers\Min\Upcx as BaseUpcx;

use ProcessWire\Page, ProcessWire\ItmPricing as PricingCRUD;
use ItemPricingQuery, ItemPricing;

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

		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$itmPricing = self::getItmPricing();
		$itmPricing->init_configs();

		if ($data->action) {
			$itmPricing->process_input($input);
			$data->whseID = $data->action == 'remove-itm-whse' ? '' : $data->whseID;
		}

		self::pw('session')->redirect($page->itm_warehouseURL($data->itemID, $data->whseID), $http301 = false);
	}

	public static function warehouse($data) {
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
		$page->headline = "Warehouses for $data->itemID";
		$html .= $config->twig->render('items/itm/warehouse/list-display.twig', ['itmw' => $itmw, 'itemID' => $data->itemID, 'item' => $itm->item($data->itemID), 'warehouses' => $itmw->get_itemwarehouses($data->itemID)]);
		return $html;
	}

	public static function getItmWarehouse() {
		return self::pw('modules')->get('ItmWarehouse');
	}

}
