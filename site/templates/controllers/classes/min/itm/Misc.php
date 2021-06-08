<?php namespace Controllers\Min\Itm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\ItmMisc as MiscCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Misc extends ItmFunction {
	const PERMISSION_ITMP = 'misc';

	private static $misc;

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		self::getItmMisc()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::misc($data);
		}
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$fields   = ['itemID|text', 'action|text'];
		$data    = self::sanitizeParameters($data, $fields);
		$input   = self::pw('input');
		$itmMiscisc = self::getItmMisc();
		$itmMiscisc->init_configs();

		if ($data->action) {
			$itmMiscisc->process_input($input);
		}

		self::pw('session')->redirect(self::itmUrlMisc($data->itemID), $http301 = false);
	}

	public static function misc($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
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
		$itmMisc    = self::getItmMisc();
		$item = $itm->get_item($data->itemID);
		$page->headline = "ITM: $data->itemID Misc";
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/misc/page.twig', ['itm' => $itmMisc, 'item' => $item, 'recordlocker' => $itm->recordlocker]);
		$page->js   .= $config->twig->render('items/itm/misc/js.twig', ['itm' => $itmMisc]);
		return $html;
	}

	public static function getItmMisc() {
		if (empty(self::$misc)) {
			self::$misc = self::pw('modules')->get('ItmMisc');
			self::$misc->init();
		}
		return self::$misc;
	}
}
