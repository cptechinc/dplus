<?php namespace Controllers\Min\Itm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\ItmCosting as CostingCRUD;

class Costing extends Base {
	const PERMISSION_ITMP = 'costing';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::getItmCosting()->initConfigs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::costing($data);
		}
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields     = ['itemID|text', 'action|text', 'redirect|text'];
		$data       = self::sanitizeParameters($data, $fields);
		$input      = self::pw('input');
		$itmCosting = self::getItmCosting();
		$itmCosting->initConfigs();

		if ($data->action) {
			$itmCosting->processInput($input);
		}

		if (self::pw('config')->ajax === false) {
			$url = empty($data->redirect) === false ? $data->redirect : self::itmUrlCosting($data->itemID);
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	private static function costing($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$page = self::pw('page');
		$page->headline = "ITM: $data->itemID Costing";
		$page->js .= self::pw('config')->twig->render('items/itm/costing/js.twig', ['costing' => self::getItmCosting(), 'itm' => self::getItm()]);
		return self::costingDisplay($data);
	}

/* =============================================================
	Displays
============================================================= */
	private static function costingDisplay($data) {
		$config  = self::pw('config');
		$itm     = self::getItm();
		$itmC    = self::getItmCosting();
		$item = $itm->item($data->itemID);
		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');

		if ($itm->getResponse()) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $itm->getResponse()]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/costing/page.twig', ['itm' => $itm, 'item' => $item, 'm_costing' => $itmC]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getItmCosting() {
		return self::pw('modules')->get('ItmCosting');
	}
}
