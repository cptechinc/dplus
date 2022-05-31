<?php namespace Controllers\Min\Inmain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Warehouse;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Iwhm as IwhmManager;
// Mvc Controllers
use Controllers\Min\Base;

class Iwhm extends Base {
	const DPLUSPERMISSION = 'iwhm';
	const SHOWONPAGE = 10;

	private static $iwhm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::displayAlertUserPermission($data);
		}
		// Sanitize Params, parse route from params
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->code) === false) {
			return self::code($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['code|text', 'whseID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::iwhmUrl();
		$iwhm = self::getIwhm();

		if ($data->action) {
			switch ($data->action) {
				case 'update-notes':
				case 'delete-notes':
					$iwhm->qnotes->processInput(self::pw('input'));
					$url = self::codeEditUrl($data->whseID);
					break;
				default:
					$iwhm->processInput(self::pw('input'));
					if ($data->action != 'delete') {
						$url = self::codeEditUrl($data->code);
					}
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::getIwhm()->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->headline = "Warehouse";

		$filter = new Filters\Min\Warehouse();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "IWHM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/min/iwhm/list/.js.twig');
		$html = self::displayList($data, $codes);
		self::getIwhm()->deleteResponse();
		self::getIwhm()->qnotes->deleteResponses();
		return $html;
	}

	private static function code($data) {
		self::pw('page')->headline = "IWHM: Adding New Warehouse";

		$iwhm = self::getIwhm();
		$iwhm->initFieldAttributes();
		$warehouse = $iwhm->getOrCreate($data->code);

		if ($warehouse->isNew() === false) {
			self::pw('page')->headline = "IWHM: Editing $data->code";
			$iwhm->lockrecord($warehouse);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/iwhm/edit/.js.twig', ['iwhm' => $iwhm]);

		if ($warehouse->isNew() === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
			self::pw('page')->js .= self::pw('config')->twig->render('code-tables/min/iwhm/edit/qnotes/js.twig', ['iwhm' => $iwhm]);
		}
		$html = self::displayCode($data, $warehouse);
		self::getIwhm()->deleteResponse();
		self::getIwhm()->qnotes->deleteResponses();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::iwhmUrl();
	}

	public static function iwhmUrl($code = '') {
		if (empty($code)) {
			return self::url();
		}
		return self::iwhmFocusUrl($code);
	}

	public static function iwhmFocusUrl($focus) {
		$filter = new Filters\Min\Warehouse();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'iwhm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeEditUrl($code) {
		$url = new Purl(Menu::iwhmUrl());
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(self::codeEditUrl($code));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$iwhm = self::getIwhm();

		$html  = '';
		$html .= $config->twig->render('code-tables/min/iwhm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= $config->twig->render('code-tables/min/iwhm/list.twig', ['manager' => $iwhm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $iwhm]);
		return $html;
	}

	private static function displayCode($data, Warehouse $warehouse) {
		$config = self::pw('config');
		$iwhm = self::getIwhm();

		$html  = '';
		$html .= $config->twig->render('code-tables/min/iwhm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayLocked($data).'</div>';
		$html .= '<div class="mb-3">'.$config->twig->render('code-tables/min/iwhm/edit/display.twig', ['iwhm' => $iwhm, 'warehouse' => $warehouse]).'</div>';

		if ($warehouse->isNew() === false) {
			$html .= $config->twig->render('code-tables/min/iwhm/edit/qnotes/display.twig', ['iwhm' => $iwhm, 'qnotes' => $iwhm->qnotes, 'warehouse' => $warehouse]);
			$html .= $config->twig->render('msa/noce/ajax/notes-modal.twig');
		}
		return $html;
	}

	private static function displayResponse($data) {
		$iwhm = self::getIwhm();
		$response = $iwhm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayResponseQnotes($data) {
		$iwhm = self::getIwhm();
		$html = '';

		foreach ($iwhm->qnotes->getResponses() as $response) {
			$html .= '<div class="mb-3">'. self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]) .'</div>';
		}
		return $html;
	}

	private static function displayLocked($data) {
		$iwhm = self::getIwhm();

		if ($iwhm->recordlocker->isLocked($data->code) && $iwhm->recordlocker->userHasLocked($data->code) === false) {
			$msg = "Warehouse $data->code is being locked by " . $iwhm->recordlocker->getLockingUser($data->code);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $data->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=inmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=inmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=inmain)::codeListUrl', function($event) {
			$event->return = self::iwhmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::codeAddUrl', function($event) {
			$event->return = self::codeEditUrl('new');
		});

		$m->addHook('Page(pw_template=inmain)::codeEditUrl', function($event) {
			$event->return = self::codeEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getIwhm() {
		if (empty(self::$iwhm)) {
			self::$iwhm = new IwhmManager();
		}
		return self::$iwhm;
	}
}
