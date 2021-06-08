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
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		self::getItmPricing()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$page->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::pricing($data);
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
		$itmPricing = self::getItmPricing();
		$itmPricing->init_configs();

		if ($data->action) {
			$itmPricing->process_input($input);
		}

		self::pw('session')->redirect(self::itmUrlPricing($data->itemID), $http301 = false);
	}

	public static function pricing($data) {
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
		$itmPricing = self::getItmPricing();
		$item = $itm->get_item($data->itemID);
		$pricing = $itmPricing->get_pricing($data->itemID);
		$page->headline = "ITM: $data->itemID Pricing";
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/pricing/display.twig', ['item' => $item, 'pricingm' => $itmPricing, 'item_pricing' => $pricing, 'itm' => $itm]);
		$page->js .= $config->twig->render('items/itm/pricing/js.twig', ['item_pricing' => $pricing]);
		self::pw('session')->remove('response_itm');
		return $html;
	}

	public static function getItmPricing() {
		return self::pw('modules')->get('ItmPricing');
	}
}
