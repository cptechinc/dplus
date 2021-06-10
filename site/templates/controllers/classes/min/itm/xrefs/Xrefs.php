<?php namespace Controllers\Min\Itm;
use Purl\Url as Purl;

// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Mvc Controllers
use Controllers\Min\Itm\Xrefs\XrefFunction;

class Xrefs extends XrefFunction {
	const PERMISSION_ITMP = 'xrefs';

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
		self::pw('session')->redirect(self::itmUrlXrefs($data->itemID), $http301 = false);
	}

	public static function itmXrefs($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		self::initHooks();
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
		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
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
/* =============================================================
	URLs
============================================================= */
	public static function xrefsUrl($itemID) {
		return self::itmUrlFunction($itemID, 'xrefs');
	}

	public static function xrefUrlFunction($itemID, $function = '') {
		$url = new Purl(self::xrefsUrl($itemID));
		if ($function) {
			$url->path->add($function);
		}
		return $url->getUrl();
	}

	public static function xrefUrlUpcx($itemID) {
		return self::xrefUrlFunction($itemID, 'upcx');
	}

	public static function xrefUrlVxm($itemID) {
		return self::xrefUrlFunction($itemID, 'vxm');
	}

	public static function xrefUrlCxm($itemID) {
		return self::xrefUrlFunction($itemID, 'cxm');
	}

	public static function xrefUrlKim($itemID) {
		return self::xrefUrlFunction($itemID, 'kim');
	}

	public static function xrefUrlMxrfe($itemID) {
		return self::xrefUrlFunction($itemID, 'mxrfe');
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('ItmXrefs');

		$m->addHook('Page(pw_template=itm)::xrefUrlUpcx', function($event) {
			$event->return = self::xrefUrlUpcx($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::xrefUrlVxm', function($event) {
			$event->return = self::xrefUrlVxm($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::xrefUrlCxm', function($event) {
			$event->return = self::xrefUrlCxm($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::xrefUrlKim', function($event) {
			$event->return = self::xrefUrlKim($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::xrefUrlMxrfe', function($event) {
			$event->return = self::xrefUrlMxrfe($event->arguments(0));
		});
	}
}
