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
		self::sanitizeParametersShort($data, ['itemID|text', 'component|text', 'action|text']);

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

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		self::sanitizeParametersShort($data, ['bomID|text', 'itemID|text', 'action|text']);

		if (empty($data->bomID)) {
			self::setupInputBomid($data);
		}
		$bmm = BmmParent::getBmm();

		if ($data->action) {
			$bmm->processInput(self::pw('input'));
		}
		self::pw('session')->redirect(self::bomUrl($data->itemID), $http301 = false);
	}

	private static function setupInputBomid($data) {
		$data->bomID = $data->itemID;
		$input = self::pw('input');
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$values->bomID = $data->itemID;
	}

	private static function bom($data) {
		self::pw('page')->headline = "ITM: BoM $data->itemID";
		$html = self::displayBom($data);
		return $html;
	}

	private static function bomComponent($data) {
		$fields = ['itemID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		$bmm  = BmmParent::getBmm();
		$component = $bmm->components->getOrCreate($data->itemID, $data->component);

		if ($component->isNew() === false) {
			$bmm->lockrecord($data->itemID);
		}

		$page           = self::pw('page');
		$page->headline = $component->isNew() ? "ITM: BoM $data->itemID" : "ITM: BoM $data->itemID - $data->component";
		$page->js .= self::pw('config')->twig->render('mpm/bmm/component/js.twig', ['bmm' => $bmm]);
		$html = self::displayBomComponent($data, $component);
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayBom($data) {
		$bmm  = BmmParent::getBmm();
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		$bomItem  = $bmm->header->getOrCreate($data->itemID);
		self::initHooks();
		BmmParent::lock($data->itemID);
		$html = '';
		// $html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= BmmParent::displayLock($data);
		$html .= BmmParent::displayResponse($data);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/bom/bom/display.twig', ['item' => $item, 'itm' => $itm, 'bmm' => $bmm, 'bomItem' => $bomItem]);
		$bmm::deleteResponse();
		return $html;
	}

	private static function displayBomComponent($data, $component) {
		$data->bomID = $data->itemID;
		$bmm      = BmmParent::getBmm();
		$itm      = self::getItm();
		$item     = $itm->item($data->itemID);
		$bomItem  = $bmm->header->getOrCreate($data->itemID);
		self::initHooks();
		BmmParent::lock($data->itemID);

		$html  = '';
		// $html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= BmmParent::displayLock($data);
		$html .= BmmParent::displayResponse($data);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/bom/component/display.twig', ['item' => $item, 'bmm' => $bmm, 'itm' => $itm, 'bomItem' => $bomItem, 'component' => $component]);
		$bmm::deleteResponse();
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function bomUrl($itemID, $focus = '') {
		$url = new Purl(Xrefs::xrefUrlBom($itemID));
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function bomComponentUrl($bomID, $component) {
		$url = new Purl(self::bomUrl($bomID));
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

		$m->addHook('Page(pw_template=itm)::bomUrl', function($event) {
			$bomID = $event->arguments(0);
			$focus = $event->arguments(1);
			$event->return = self::bomUrl($bomID, $focus);
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

		$m->addHook('Page(pw_template=itm)::bomComponentExitUrl', function($event) {
			$bomID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::bomUrl($bomID, $component);
		});
	}
}
