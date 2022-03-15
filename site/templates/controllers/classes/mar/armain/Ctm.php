<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ArCustTypeCode;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mar\Ctm as CtmManager;
// Mvc Controllers
use Controllers\Mar\Armain\Base;

class Ctm extends Base {
	const DPLUSPERMISSION = 'ctm';
	const SHOWONPAGE = 20;

	private static $ctm;

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
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::ctmUrl();
		$ctm = self::getCtm();

		if ($data->action) {
			switch ($data->action) {
				case 'update-notes':
				case 'delete-notes':
					$ctm->qnotes->processInput(self::pw('input'));
					$url = self::codeEditUrl($data->code);
					break;
				default:
					$ctm->processInput(self::pw('input'));
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
		self::getCtm()->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->headline = "Customer Type Code";

		$filter = new Filters\Mar\ArCustTypeCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "CTM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mar/ctm/list/.js.twig');
		$html = self::displayList($data, $codes);
		self::getCtm()->deleteResponse();
		self::getCtm()->qnotes->deleteResponses();
		return $html;
	}

	private static function code($data) {
		self::pw('page')->headline = "CTM: Adding New Type";

		$ctm = self::getCtm();
		$code = $ctm->getOrCreate($data->code);

		if ($code->isNew() === false) {
			self::pw('page')->headline = "CTM: Editing $data->code";
			$ctm->lockrecord($code);
		}
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mar/ctm/edit/.js.twig', ['ctm' => $ctm]);

		if ($code->isNew() === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
			self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mar/ctm/edit/qnotes/js.twig', ['ctm' => $ctm]);
		}
		$html = self::displayCode($data, $code);
		self::getCtm()->deleteResponse();
		self::getCtm()->qnotes->deleteResponses();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::ctmUrl();
	}

	public static function ctmUrl($code = '') {
		if (empty($code)) {
			return self::url();
		}
		return self::ctmFocusUrl($code);
	}

	public static function ctmFocusUrl($focus) {
		$filter = new Filters\Mar\ArCustTypeCode();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'ctm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeEditUrl($code) {
		$url = new Purl(Menu::ctmUrl());
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
		$ctm = self::getCtm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mar/ctm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= $config->twig->render('code-tables/mar/ctm/list.twig', ['manager' => $ctm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $ctm]);
		return $html;
	}

	private static function displayCode($data, ArCustTypeCode $code) {
		$config = self::pw('config');
		$ctm = self::getCtm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mar/ctm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayLocked($data).'</div>';
		$html .= '<div class="mb-3">'.$config->twig->render('code-tables/mar/ctm/edit/display.twig', ['ctm' => $ctm, 'code' => $code]).'</div>';

		if ($code->isNew() === false) {
			$html .= $config->twig->render('code-tables/mar/ctm/edit/qnotes/display.twig', ['ctm' => $ctm, 'qnotes' => $ctm->qnotes, 'code' => $code]);
			$html .= $config->twig->render('msa/noce/ajax/notes-modal.twig');
		}
		return $html;
	}

	private static function displayResponse($data) {
		$ctm = self::getCtm();
		$response = $ctm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayResponseQnotes($data) {
		$ctm = self::getCtm();
		$html = '';

		foreach ($ctm->qnotes->getResponses() as $response) {
			$html .= '<div class="mb-3">'. self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]) .'</div>';
		}
		return $html;
	}


	private static function displayLocked($data) {
		$ctm = self::getCtm();

		if ($ctm->recordlocker->isLocked($data->code) && $ctm->recordlocker->userHasLocked($data->code) === false) {
			$msg = "ArCustTypeCode $data->code is being locked by " . $ctm->recordlocker->getLockingUser($data->code);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ArCustTypeCode $data->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=armain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=armain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=armain)::codeListUrl', function($event) {
			$event->return = self::ctmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::codeAddUrl', function($event) {
			$event->return = self::codeEditUrl('new');
		});

		$m->addHook('Page(pw_template=armain)::codeEditUrl', function($event) {
			$event->return = self::codeEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCtm() {
		if (empty(self::$ctm)) {
			self::$ctm = new CtmManager();
		}
		return self::$ctm;
	}
}
