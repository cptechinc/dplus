<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;


class Xrefs extends Base {
	const PERMISSION_ITMP = 'xrefs';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		return self::itmXrefs($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);

		if ($data->action) {
			$itmXrefs = self::pw('modules')->get('ItmXrefs');
			$itmXrefs->processInput(self::pw('input'));
		}
		self::pw('session')->redirect(self::itmUrlXrefs($data->itemID), $http301 = false);
	}

	private static function itmXrefs($data) {
		self::initHooks();
		$page    = self::pw('page');
		$itm     = self::getItm();
		$item = $itm->item($data->itemID);
		$html = '';

		$page->headline = "ITM: $data->itemID X-Refs";
		$page->js .= self::pw('config')->twig->render('items/itm/xrefs/js.twig');
		return self::display($data, $item);
	}

/* =============================================================
	Displays
============================================================= */
	private static function display($data, ItemMasterItem $item) {
		$itm     = self::getItm();
		$xrefs   = self::xrefs();
		$session = self::pw('session');
		$config  = self::pw('config');

		$html = '';
		$html .= self::breadCrumbs();
		if ($session->getFor('response', 'cxm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'cxm')]);
			$session->removeFor('response', 'cxm');
		}
		if ($itm->getResponse()) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $itm->getResponse()]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/xrefs/page.twig', ['itm' => $itm, 'item' => $item, 'xrefs' => $xrefs]);
		return $html;
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

		$m->addHook('Page(pw_template=itm)::xrefUrlSubstitutes', function($event) {
			$event->return = self::xrefUrlSubstitutes($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::xrefUrlBom', function($event) {
			$event->return = self::xrefUrlBom($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function xrefs() {
		$modules = self::pw('modules');
		$xrefs   = new WireData();
		$xrefs->cxm  = $modules->get('XrefCxm');
		$xrefs->upcx = $modules->get('XrefUpc');
		return $xrefs;
	}
}
