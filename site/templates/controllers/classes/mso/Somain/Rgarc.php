<?php namespace Controllers\Mso\Somain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mso\Rgarc as RgarcManager;
// Mvc Controllers
use Controllers\Mso\Somain\Base;

class Rgarc extends Base {
	const DPLUSPERMISSION = 'rgarc';
	const SHOWONPAGE = 10;

	private static $rgarc;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'RGA/Return Reason Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::rgarcUrl();
		$rgarc = self::getRgarc();

		if ($data->action) {
			$rgarc->processInput(self::pw('input'));
			$url = self::rgarcUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "RGA/Return Reason Code";

		$filter = new Filters\Mso\SoReasonCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "RGARC: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= self::pw('config')->twig->render('code-tables/mso/rgarc/.js.twig', ['rgarc' => self::getRgarc()]);
		$html = self::displayList($data, $codes);
		self::getRgarc()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function rgarcUrl($code = '') {
		if (empty($code)) {
			return Menu::rgarcUrl();
		}
		return self::rgarcFocusUrl($code);
	}

	public static function rgarcFocusUrl($focus) {
		$filter = new Filters\Mso\SoReasonCode();
		if ($filter->exists($focus) === false) {
			return Menu::rgarcUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::rgarcUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'rgarc', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::rgarcUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$rgarc = self::getRgarc();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $rgarc, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/edit-modal.twig', ['manager' => $rgarc]);
		return $html;
	}

	public static function displayResponse($data) {
		$rgarc = self::getRgarc();
		$response = $rgarc->getResponse();
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

		$m->addHook('Page(pw_template=somain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=somain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=somain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getRgarc() {
		if (empty(self::$rgarc)) {
			self::$rgarc = new RgarcManager();
		}
		return self::$rgarc;
	}
}
