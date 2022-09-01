<?php namespace Controllers\Min\Inmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Min\Iarn as CodeTable;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Iarn extends Base {
	const DPLUSPERMISSION = 'iarn';
	const SHOWONPAGE = 10;
	const TITLE   = 'Inventory Adjustment Reason';
	const SUMMARY = 'View / Edit Inventory Adjustment Reasons';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = CodeTable::DESCRIPTION;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::iarnUrl();
		$codeTable = self::getCodeTable();

		if ($data->action) {
			$codeTable->processInput(self::pw('input'));
			$url = self::iarnUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');

		$filter = self::getCodesFilter();

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= self::pw('config')->twig->render('code-tables/min/iarn/.js.twig', ['manager' => self::getCodeTable()]);
		$html = self::displayList($data, $codes);
		self::getCodeTable()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function iarnUrl($code = '') {
		if (empty($code)) {
			return Menu::iarnUrl();
		}
		return self::iarnFocusUrl($code);
	}

	public static function iarnFocusUrl($focus) {
		$filter = self::getCodesFilter();
		if ($filter->exists($focus) === false) {
			return Menu::iarnUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::iarnUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'iarn', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::iarnUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$codeTable = self::getCodeTable();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/min/iarn/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/min/iarn/edit-modal.twig', ['manager' => $codeTable]);
		return $html;
	}

	public static function displayResponse($data) {
		$codeTable = self::getCodeTable();
		$response = $codeTable->getResponse();
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

		$m->addHook('Page(pw_template=inmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=inmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=inmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCodeTable() {
		return CodeTable::instance();
	}

	public static function getCodesFilter() {
		return new Filters\Min\InvAdjustmentReason();
	}
}
