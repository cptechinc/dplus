<?php namespace Controllers\Min\Itm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page;
use Dplus\Min\Inmain\Itm\Dimensions as DimensionsCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Dimensions extends ItmFunction {
	const PERMISSION_ITMP = '';

	private static $dim;

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::dim($data);
		}
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$fields   = ['itemID|text', 'action|text'];
		$data    = self::sanitizeParameters($data, $fields);
		$input   = self::pw('input');
		$itmDim = self::getItmDim();

		if ($data->action) {
			$itmDim->process_input($input);
		}

		self::pw('session')->redirect(self::itmUrlMisc($data->itemID), $http301 = false);
	}

	public static function dim($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}

		$page = self::pw('page');
		$page->headline = "ITM: $data->itemID Dimensions";
		// $page->js .= self::pw('config')->twig->render('items/itm/misc/js.twig', ['itm' => self::getItmDim()]);
		return self::dimDisplay($data);
	}

	private static function dimDisplay($data) {
		$config  = self::pw('config');
		$session = self::pw('session');
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$dim  = self::getItmDim()->getOrCreateDimension($data->itemID);

		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/dimensions/display.twig', ['itmDimensions' => self::getItmDim(), 'dimensions' => $dim, 'item' => $item]);
		return $html;
	}

	public static function getItmDim() {
		if (empty(self::$dim)) {
			self::$dim = new DimensionsCRUD();
			// self::$dim->init();
		}
		return self::$dim;
	}
}
