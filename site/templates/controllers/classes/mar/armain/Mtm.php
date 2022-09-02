<?php namespace Controllers\Mar\Armain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mar\Mtm as RecordManager;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Mtm extends Base {
	const DPLUSPERMISSION = 'mtm';
	const SHOWONPAGE = 10;
	const TITLE   = 'Master Tax Code';
	const SUMMARY = 'View / Edit Master Tax Codes';

	private static $recordsManager;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = RecordManager::DESCRIPTION;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::mtmUrl();
		$recordsManager = self::getRecordManager();

		if ($data->action) {
			$recordsManager->processInput(self::pw('input'));
			$url = self::mtmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');

		$filter = new Filters\Mar\ArTaxCode();

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= self::pw('config')->twig->render('code-tables/mar/mtm/.js.twig', ['mtm' => self::getRecordManager()]);
		$html = self::displayList($data, $codes);
		self::getRecordManager()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function mtmUrl($code = '') {
		if (empty($code)) {
			return Menu::mtmUrl();
		}
		return self::mtmFocusUrl($code);
	}

	public static function mtmFocusUrl($focus) {
		$filter = new Filters\Mar\ArTaxCode();
		if ($filter->exists($focus) === false) {
			return Menu::mtmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::mtmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'mtm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::mtmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$recordsManager = self::getRecordManager();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mar/mtm/list.twig', ['manager' => $recordsManager, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mar/mtm/edit-modal.twig', ['manager' => $recordsManager]);
		return $html;
	}

	public static function displayResponse($data) {
		$recordsManager = self::getRecordManager();
		$response = $recordsManager->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
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

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getRecordManager() {
		if (empty(self::$recordsManager)) {
			self::$recordsManager = new RecordManager();
		}
		return self::$recordsManager;
	}
}
