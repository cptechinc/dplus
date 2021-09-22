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
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::getItmMisc();

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
			return self::displayAlertUserPermission($data);;
		}
		$fields   = ['itemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
		$input   = self::pw('input');
		$itmMisc = self::getItmMisc();

		if ($data->action) {
			$itmMisc->processInput($input);
		}

		if (self::pw('config')->ajax === false) {
			self::pw('session')->redirect(self::itmUrlMisc($data->itemID), $http301 = false);
		}
	}

	public static function misc($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}

		$page = self::pw('page');
		$page->headline = "ITM: $data->itemID Misc";
		$page->js .= self::pw('config')->twig->render('items/itm/misc/js.twig', ['itm' => self::getItmMisc()]);
		return self::miscDisplay($data);
	}

	private static function miscDisplay($data) {
		$config  = self::pw('config');
		$session = self::pw('session');
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);

		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		if ($session->getFor('response', 'itm-hazmat')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm-hazmat')]);
			$session->removeFor('response', 'itm-hazmat');
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/misc/page.twig', ['itm' => self::getItmMisc(), 'item' => $item, 'recordlocker' => $itm->recordlocker]);
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
