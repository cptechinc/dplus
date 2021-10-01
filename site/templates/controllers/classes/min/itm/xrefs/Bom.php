<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use BomItemQuery, BomItem;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Mvc Controllers
use Controllers\Mpm\Pmmain\Bmm as BmmParent;

class Bom extends Base {
	const PERMISSION_ITMP = 'xrefs';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->component) === false) {
			return self::bomComponent($data);
		}
		return self::bom($data);
	}

	// public static function handleCRUD($data) {
	// 	if (self::validateItemidAndPermission($data) === false) {
	// 		return self::displayAlertUserPermission($data);
	// 	}
	// 	self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);
	// 	$bmm = BmmParent::getBom();
	//
	// 	if ($data->action) {
	// 		$bmm->process_input(self::pw('input'));
	// 	}
	// 	self::pw('session')->redirect(self::BomUrl($data->itemID), $http301 = false);
	// }

	private static function bom($data) {
		self::pw('page')->headline = "ITM: BoM $data->itemID";
		$html = self::displayBom($data);
		self::pw('session')->removeFor('response', 'bom');
		return $html;
	}

	private static function bomComponent($data) {
		$fields = ['itemID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		$bmm  = BmmParent::getBmm();
		$component = $bmm->components->getOrCreate($data->itemID, $data->component);

		$page           = self::pw('page');
		$page->headline = $component->isNew() ? "ITM: BoM $data->itemID" : "ITM: BoM $data->itemID - $data->component";
		// $page->js       .= self::pw('config')->twig->render('mki/kim/kit/component/js.twig', ['kim' => $bmm]);
		$html = self::displayBomComponent($data, $component);
		// self::pw('session')->removeFor('response', 'kim');
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayBom($data) {
		$bmm  = BmmParent::getBmm();
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		$bomItem  = $bmm->header->header($data->itemID);
		self::initHooks();

		$html = '';
		// $html .= self::kitHeaders();
		// $html .= self::lockItem($data->itemID);
		// $html .= BmmParent::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/bom/bom/display.twig', ['item' => $item, 'bmm' => $bmm, 'bomItem' => $bomItem]);
		return $html;
	}

	private static function displayBomComponent($data, $component) {
		$bmm      = BmmParent::getBmm();
		$itm      = self::getItm();
		$item     = $itm->item($data->itemID);
		$bomItem  = $bmm->header->header($data->itemID);
		self::initHooks();

		$html  = '';
		// $html .= self::kitHeaders();
		// $html .= self::lockItem($data->itemID);
		// $html .= BmmParent::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/bom/component/display.twig', ['item' => $item, 'bmm' => $bmm, 'bomItem' => $bomItem, 'component' => $component]);
		return $html;
	}

	//
	// private static function kitHeaders() {
	// 	$session = self::pw('session');
	// 	$config  = self::pw('config');
	//
	// 	$html  = '';
	// 	$html .= self::breadCrumbs();
	//
	// 	if ($session->getFor('response', 'itm')) {
	// 		$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
	// 	}
	//
	// 	if ($session->getFor('response','kim')) {
	// 		$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','kim')]);
	// 	}
	// 	return $html;
	// }

/* =============================================================
	URL Functions
============================================================= */
	public static function BomUrl($itemID, $focus = '') {
		$url = new Purl(Xrefs::xrefUrlBom($itemID));
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function bomComponentUrl($bomID, $component) {
		$url = new Purl(self::BomUrl($bomID));
		$url->query->set('component', $component);
		return $url->getUrl();
	}

	public static function bomComponentDeleteUrl($bomID, $component) {
		$url = new Purl(self::bomComponentUrl($bomID, $component));
		$url->query->set('action', 'delete-component');
		return $url->getUrl();
	}

/* =============================================================
	Hooks Functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=itm)::BomUrl', function($event) {
			$bomID = $event->arguments(0);
			$focus = $event->arguments(1);
			$event->return = self::BomUrl($bomID, $focus);
		});

		$m->addHook('Page(pw_template=itm)::bomExitUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = Xrefs::xrefsUrl($itemID);
		});

		$m->addHook('Page(pw_template=itm)::bomDeleteUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::bomListUrl($focus);
		});

		$m->addHook('Page(pw_template=itm)::bomComponentUrl', function($event) {
			$bomID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::bomComponentUrl($bomID, $component);
		});

		$m->addHook('Page(pw_template=itm)::bomComponentDeleteUrl', function($event) {
			$bomID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::bomComponentDeleteUrl($bomID, $component);
		});
	}
}
