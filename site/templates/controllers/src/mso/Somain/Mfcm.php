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
use Dplus\Codes\Mso\Mfcm as MfcmManager;
// Mvc Controllers
use Controllers\Mso\Somain\Base;

class Mfcm extends Base {
	const DPLUSPERMISSION = 'mfcm';
	const SHOWONPAGE = 10;

	private static $mfcm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = 'Motor Freight Code';

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::mfcmUrl();
		$mfcm  = self::getMfcm();

		if ($data->action) {
			$mfcm->processInput(self::pw('input'));
			$url = self::mfcmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$page->headline = "Motor Freight Code";

		$filter = new Filters\Mso\MotorFreightCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "MFCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mso/mfcm/.js.twig', ['mfcm' => self::getMfcm()]);
		$html = self::displayList($data, $codes);
		self::getMfcm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function mfcmUrl($code = '') {
		if (empty($code)) {
			return Menu::mfcmUrl();
		}
		return self::mfcmFocusUrl($code);
	}

	public static function mfcmFocusUrl($focus) {
		$filter = new Filters\Mso\MotorFreightCode();
		if ($filter->exists($focus) === false) {
			return Menu::mfcmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::mfcmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'mfcm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::mfcmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$mfcm = self::getMfcm();

		$html  = '';
		$html .= $config->twig->render('code-tables/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/mso/mfcm/list.twig', ['manager' => $mfcm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mso/mfcm/edit-modal.twig', ['manager' => $mfcm]);
		return $html;
	}

	public static function displayResponse($data) {
		$mfcm = self::getMfcm();
		$response = $mfcm->getResponse();
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
	public static function getMfcm() {
		if (empty(self::$mfcm)) {
			self::$mfcm = new MfcmManager();
		}
		return self::$mfcm;
	}
}
