<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use PrWorkCenter;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mpm\Dcm as DcmManager;

class Dcm extends Base {
	const DPLUSPERMISSION = 'dcm';

	private static $dcm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::dcmUrl();
		$dcm  = self::getDcm();

		if ($data->action) {
			$dcm->processInput(self::pw('input'));
			$url  = self::dcmUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Mpm\PrWorkCenter();

		$page->headline = "Work Center";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "DCM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('code-tables/mpm/dcm/.js.twig', ['dcm' => self::getDcm()]);
		$html = self::displayList($data, $codes);
		self::getDcm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function dcmUrl($code = '') {
		if (empty($code)) {
			return Menu::dcmUrl();
		}
		return self::dcmFocusUrl($code);
	}

	public static function dcmFocusUrl($focus) {
		$filter = new Filters\Mpm\PrWorkCenter();
		if ($filter->exists($focus) === false) {
			return Menu::dcmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::dcmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'dcm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::dcmUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$config = self::pw('config');
		$dcm = self::getDcm();

		$html  = '';
		$html .= $config->twig->render('code-tables/mpm/dcm/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $dcm, 'codes' => $codes]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= $config->twig->render('code-tables/mpm/dcm/edit-modal.twig', ['dcm' => $dcm]);
		return $html;
	}

	public static function displayResponse($data) {
		$dcm = self::getDcm();
		$response = $dcm->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpm');

		$m->addHook('Page(pw_template=mpm)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=mpm)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=mpm)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getDcm() {
		if (empty(self::$dcm)) {
			self::$dcm = new DcmManager();
		}
		return self::$dcm;
	}
}
