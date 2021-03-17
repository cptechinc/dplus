<?php namespace Controllers\Min\Itm;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Xrefs extends ItmFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		return self::itmXrefs($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		if ($data->action) {
			$itmXrefs = self::pw('modules')->get('ItmXrefs');
			$itmXrefs->process_input($input);
		}
		self::pw('session')->redirect(self::pw('page')->itm_xrefsURL($data->itemID), $http301 = false);
	}

	public static function itmXrefs($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$itm     = self::getItm();
		$xrefs   = self::xrefs();
		$session = self::pw('session');
		$item = $itm->get_item($data->itemID);
		$html = '';
		if ($session->response_xref) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
			$session->remove('response_xref');
		}
		$page->headline = "ITM: $data->itemID X-refs";
		$html .= self::breadCrumbs();
		$html .= Itm::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig', ['page_itm' => $page->parent]);
		$html .= $config->twig->render('items/itm/xrefs/page.twig', ['itm' => $itm, 'item' => $item, 'xrefs' => $xrefs]);
		$page->js   .= $config->twig->render('items/itm/xrefs/js.twig');
		return $html;
	}

	private static function xrefs() {
		$modules = self::pw('modules');
		$xrefs   = new WireData();
		$xrefs->cxm  = $modules->get('XrefCxm');
		$xrefs->upcx = $modules->get('XrefUpc');
		return $xrefs;
	}
}
