<?php namespace Controllers\Min\Itm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\ItmCosting as CostingCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Costing extends ItmFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		self::getItmCosting()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$page->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::costing($data);
		}
	}

	public static function handleCRUD($data) {

		$page = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$itmPricing = self::getItmCosting();
		$itmPricing->init_configs();

		if ($data->action) {
			$itmPricing->process_input($input);
		}

		self::pw('session')->redirect($page->itm_pricingURL($data->itemID), $http301 = false);
	}

	public static function costing($data) {
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
		$session = self::pw('session');
		$itm     = self::getItm();
		$itmC    = self::getItmCosting();
		$item = $itm->get_item($data->itemID);
		$page->headline = "ITM: $data->itemID Costing";
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= Itm::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig', ['page_itm' => $page->parent]);
		$html .= $config->twig->render('items/itm/costing/page.twig', ['itm' => $itm, 'item' => $item, 'm_costing' => $itmC, 'recordlocker' => $itm->recordlocker]);
		$page->js .= $config->twig->render('items/itm/costing/js.twig', []);
		return $html;
	}

	public static function getItmCosting() {
		return self::pw('modules')->get('ItmCosting');
	}
}
