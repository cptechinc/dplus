<?php namespace Controllers\Min\Itm\Xrefs;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Kim as KimCRUD;
// Mvc Controllers
use Controllers\Mki\Kim as KimController;

class Kim extends Base {
	const PERMISSION_ITMP = 'xrefs';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		KimController::getKim()->init_configs();

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->component) === false) {
			return self::kitComponent($data);
		}
		return self::kit($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}
		self::sanitizeParametersShort($data, ['itemID|text', 'action|text']);
		$kim = KimController::getKim();
		$kim->init_configs();

		if ($data->action) {
			$kim->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::kitUrl($data->itemID), $http301 = false);
	}

	private static function kit($data) {
		self::pw('page')->headline = "ITM: Kit $data->itemID";
		$html = self::displayKit($data);
		self::pw('session')->removeFor('response', 'kim');
		return $html;
	}

	private static function kitComponent($data) {
		$fields = ['itemID|text', 'component|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		$kim  = KimController::getKim();
		$component = $kim->component->getCreateComponent($data->itemID, $data->component);

		$page           = self::pw('page');
		$page->headline = $component->isNew() ? "ITM: Kit $data->itemID" : "ITM: Kit $data->itemID - $data->component";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/component/js.twig', ['kim' => $kim]);
		$html = self::displayKitComponent($data, $component);
		self::pw('session')->removeFor('response', 'kim');
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayKit($data) {
		$kim  = KimController::getKim();
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		$kit  = $kim->getCreateKit($data->itemID);
		self::initHooks();

		$html = '';
		$html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= KimController::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/kim/kit/display.twig', ['item' => $item, 'itm' => $itm, 'kim' => $kim, 'kit' => $kit]);
		return $html;
	}

	private static function displayKitComponent($data, $component) {
		$kim  = KimController::getKim();
		$itm  = self::getItm();
		$item = $itm->item($data->itemID);
		$kit  = $kim->getCreateKit($data->itemID);
		self::initHooks();

		$html  = '';
		$html .= self::kitHeaders();
		$html .= self::lockItem($data->itemID);
		$html .= KimController::lockKit($kit);
		$html .= self::pw('config')->twig->render('items/itm/xrefs/kim/component/display.twig', ['item' => $item, 'itm' => $itm, 'kim' => $kim, 'kit' => $kit, 'component' => $component]);
		return $html;
	}

	private static function kitHeaders() {
		$session = self::pw('session');
		$config  = self::pw('config');

		$html  = '';
		$html .= self::breadCrumbs();

		if ($session->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response', 'itm')]);
		}

		if ($session->getFor('response','kim')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','kim')]);
		}
		return $html;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function kitUrl($itemID, $focus = '') {
		$url = new Purl(Xrefs::xrefUrlKim($itemID));
		$url->query->set('kitID', $itemID);
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function kitDeleteUrl($kitID) {
		$url = new Purl(self::kitUrl($kitID));
		$url->query->set('action', 'delete-kit');
		return $url->getUrl();
	}

	public static function kitComponentUrl($kitID, $component) {
		$url = new Purl(self::kitUrl($kitID));
		$url->query->set('component', $component);
		return $url->getUrl();
	}

	public static function kitComponentDeleteUrl($kitID, $component) {
		$url = new Purl(self::kitComponentUrl($kitID, $component));
		$url->query->set('action', 'delete-component');
		return $url->getUrl();
	}

/* =============================================================
	Hooks Functions
============================================================= */
	public static function initHooks() {
		$m = KimController::getKim();

		$m->addHook('Page(pw_template=itm)::kitUrl', function($event) {
			$kitID = $event->arguments(0);
			$focus = $event->arguments(1);
			$event->return = self::kitUrl($kitID, $focus);
		});

		$m->addHook('Page(pw_template=itm)::kitExitUrl', function($event) {
			$itemID = $event->arguments(0);
			$event->return = Xrefs::xrefsUrl($itemID);
		});

		$m->addHook('Page(pw_template=itm)::kitDeleteUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::kitListUrl($focus);
		});

		$m->addHook('Page(pw_template=itm)::kitComponentUrl', function($event) {
			$kitID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::kitComponentUrl($kitID, $component);
		});

		$m->addHook('Page(pw_template=itm)::kitComponentDeleteUrl', function($event) {
			$kitID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::kitComponentDeleteUrl($kitID, $component);
		});
	}
}
