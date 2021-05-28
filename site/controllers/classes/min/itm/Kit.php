<?php namespace Controllers\Min\Itm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Kim as KimCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;
use Controllers\Mki\Kim as KimController;

class Kit extends ItmFunction {
	const PERMISSION_ITMP = 'xrefs';

	public static function index($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		KimController::getKim()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$page->show_breadcrumbs = false;

		if (empty($data->component) === false) {
			return self::kitComponent($data);
		}
		return self::kit($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);
		$kim = KimController::getKim();
		$kim->init_configs();

		if ($data->action) {
			$kim->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::pw('page')->itm_kitURL($data->itemID), $http301 = false);
	}

	public static function kit($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		self::pw('page')->headline = "ITM: Kit $data->itemID";
		$html = self::kitDisplay($data);
		return $html;
	}

	private static function kitDisplay($data) {
		$kim  = KimController::getKim();
		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);
		$kit  = $kim->kit($data->itemID);

		$html = '';
		$html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= KimController::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/kit/kit/display.twig', ['item' => $item, 'kim' => $kim, 'kit' => $kit]);
		return $html;
	}

	public static function kitComponent($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$fields = ['itemID|text', 'component|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if ($data->action) {
			return self::handleCRUD($data);
		}

		$page           = self::pw('page');
		$page->headline = $component->isNew() ? "ITM: Kit $data->itemID" : "ITM: Kit $data->itemID - $data->component";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/component/js.twig', ['kim' => $kim]);
		$html = self::kitComponentDisplay($data);
		return $html;
	}

	private static function kitComponentDisplay($data) {
		$kim     = KimController::getKim();
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$kit  = $kim->kit($data->itemID);
		$component = $kim->component->getCreateComponent($data->itemID, $data->component);

		$html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= KimController::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/kit/component/display.twig', ['item' => $item, 'kim' => $kim, 'kit' => $kit, 'component' => $component]);
	}

	private static function kitHeaders() {
		$session = self::pw('session');
		$config  = self::pw('config');
		$html = '';

		$html .= self::breadCrumbs();

		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}

		if ($session->getFor('response','kim')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','kim')]);
		}
		return $html;
	}
}
