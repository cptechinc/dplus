<?php namespace Controllers\Min\Itm;

// Dplus Model
use ItemPricingQuery, ItemPricing;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\ItmPricing as PricingCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Pricing extends ItmFunction {
	const PERMISSION_ITMP = 'pricing';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::getItmPricing()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::pricing($data);
		}
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'action|text', 'redirect|text'];
		$data = self::sanitizeParameters($data, $fields);
		$itmPricing = self::getItmPricing();
		$itmPricing->init_configs();

		if ($data->action) {
			$itmPricing->process_input(self::pw('input'));
		}

		if (self::pw('config')->ajax === false) {
			$url = empty($data->redirect) === false ? $data->redirect : self::itmUrlPricing($data->itemID);
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	public static function pricing($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$page    = self::pw('page');
		$page->headline = "ITM: $data->itemID Pricing";
		$page->js .= self::pw('config')->twig->render('items/itm/pricing/js.twig', ['itmPricing' => self::getItmPricing()]);
		return self::pricingDisplay($data);
	}

	private static function pricingDisplay($data) {
		$config  = self::pw('config');
		$session = self::pw('session');
		$itm     = self::getItm();
		$itmPricing = self::getItmPricing();
		$itmCosting = Costing::getItmCosting();
		$item = $itm->get_item($data->itemID);
		$pricing = $itmPricing->get_pricing($data->itemID);

		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/pricing/display.twig', ['item' => $item, 'pricingm' => $itmPricing, 'costingm' => $itmCosting, 'item_pricing' => $pricing, 'itm' => $itm]);
		return $html;
	}

	public static function getItmPricing() {
		return self::pw('modules')->get('ItmPricing');
	}
}
