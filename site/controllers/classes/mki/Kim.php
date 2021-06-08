<?php namespace Controllers\Mki;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Invkit;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Kim as KimCRUD;
// Dplus Filters
use Dplus\Filters\Mki\Kim as FilterKim;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Kim extends AbstractController {
	private static $kim;

	public static function index($data) {
		$fields = [
			'kitID'     => ['sanitizer' => 'text'],
			'component' => ['sanitizer' => 'text'],
			'q'         => ['sanitizer' => 'text'],
			'action'    => ['sanitizer' => 'text']
		];
		$data = self::sanitizeParameters($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->kitID) === false) {
			if (empty($data->component) === false) {
				return self::kitComponent($data);
			}
			return self::kit($data);
		}
		return self::listKits($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'kitID|text', 'component|text']);

		if ($data->action) {
			$kim = self::pw('modules')->get('Kim');
			$kim->process_input(self::pw('input'));
		}
		self::pw('session')->redirect(self::pw('page')->kitURL($data->kitID), $http301);
	}

	public static function kit($data) {
		$kim    = self::getKim();
		$kim->init_configs();
		$kit = $kim->getCreateKit($data->kitID);
		$page           = self::pw('page');
		$page->headline = "Kit Master: $kit->itemid";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/js.twig', ['kim' => $kim]);
		$html = self::kitDisplay($data, $kit);
		return $html;
	}

	private static function kitDisplay($data, Invkit $kit) {
		$config = self::pw('config');
		$kim    = self::getKim();
		$kim->init_configs();

		$html = '';
		$html .= self::kimHeader();
		$html .= self::lockKit($kit);
		if ($kit->isNew()) {
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You will be able to create this kit"]);
		}
		$html .= $config->twig->render('mki/kim/kit/page.twig', ['kim' => $kim, 'kit' => $kit]);
		return $html;
	}

	public static function kitComponent($data) {
		self::sanitizeParametersShort($data, ['action|text', 'kitID|text', 'component|text']);
		$kim    = self::getKim();
		$kim->init_configs();
		$page = self::pw('page');
		$page->headline = $data->component == 'new' ? "Kit Master: $data->kitID" : "Kit Master: $data->kitID - $data->component";
		$page->js       .= self::pw('config')->twig->render('mki/kim/kit/component/js.twig', ['kim' => $kim]);
		$html = self::kitComponentDisplay($data);
		return $html;
	}

	private static function kitComponentDisplay($data) {
		$kim    = self::getKim();
		$kim->init_configs();
		$kit = $kim->kit($data->kitID);
		$component = $kim->component->getCreateComponent($data->kitID, $data->component);

		$html = '';
		$html .= self::kimHeader();
		$html .= self::lockKit($kit);
		$html .= self::pw('config')->twig->render('mki/kim/kit/component/page.twig', ['kim' => $kim, 'kit' => $kit, 'component' => $component]);
		return $html;
	}

	public static function lockKit(InvKit $kit) {
		$config = self::pw('config');
		$kim    = self::getKim();
		$html = '';

		if ($kit->isNew() === false) {
			if ($kim->lockrecord($kit->itemid) === false) {
				$msg = "Kit $kit->itemid is being locked by " . $kim->recordlocker->getLockingUser($kit->itemid);
				$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kit->itemid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$html .= '<div class="mb-3"></div>';
			}
		}
		return $html;
	}

	public static function listKits($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$kim    = self::getKim();
		$filter = new FilterKim();
		$filter->init();
		if ($data->q) {
			$page->headline = "KIM: Searching for '$data->q'";
			$filter->search($data->q);
		}
		$kits = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::pw('page')->js   .= self::pw('config')->twig->render('mki/kim/list.js.twig', ['kim' => $kim]);
		$html = self::listKitsDisplay($data, $kits);
		return $html;
	}

	private static function listKitsDisplay($data, PropelModelPager $kits) {
		$config = self::pw('config');
		$kim    = self::getKim();

		$html = '';
		$html .= self::kimHeader();
		$html .= $config->twig->render('mki/kim/search-form.twig', ['q' => $data->q]);
		$html .= $config->twig->render('mki/kim/page.twig', ['kim' => $kim, 'kits' => $kits]);
		return $html;
	}

	private static function kimHeader() {
		$session = self::pw('session');
		$config  = self::pw('config');
		$html = '';

		$html .= $config->twig->render('mki/kim/bread-crumbs.twig', ['input' => self::pw('input')]);

		if ($session->getFor('response','kim')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','kim')]);
		}
		return $html;
	}

	public static function getKim() {
		if (empty(self::$kim)) {
			self::$kim = self::pw('modules')->get('Kim');
		}
		return self::$kim;
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function kitListUrl($focus = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=kim')->url);
		if ($focus) {
			$url->query->set('focus', $focus);
		}
		return $url->getUrl();
	}

	public static function kitUrl($kitID, $focus = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=kim')->url);
		$url->query->set('kitID', $kitID);
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
	Hook Functions
============================================================= */
	public static function init() {
		$m = self::getKim();

		$m->addHook('Page(pw_template=kim)::kitUrl', function($event) {
			$kitID = $event->arguments(0);
			$focus = $event->arguments(1);
			$event->return = self::kitUrl($kitID, $focus);
		});

		$m->addHook('Page(pw_template=kim)::kitListUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::kitListUrl($focus);
		});

		$m->addHook('Page(pw_template=kim)::kitExitUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::kitListUrl($focus);
		});

		$m->addHook('Page(pw_template=kim)::kitDeleteUrl', function($event) {
			$focus = $event->arguments(0);
			$event->return = self::kitListUrl($focus);
		});

		$m->addHook('Page(pw_template=kim)::kitComponentUrl', function($event) {
			$kitID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::kitComponentUrl($kitID, $component);
		});

		$m->addHook('Page(pw_template=kim)::kitComponentDeleteUrl', function($event) {
			$kitID = $event->arguments(0);
			$component = $event->arguments(1);
			$event->return = self::kitComponentDeleteUrl($kitID, $component);
		});
	}
}
