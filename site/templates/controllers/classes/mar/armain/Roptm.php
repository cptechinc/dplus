<?php namespace Controllers\Mar\Armain;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use MsaSysopCode;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Validators
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Roptm extends Controller {
	const SYSTEM = 'AR';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['sysop|text', 'code|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->sysop) === false) {
			return self::sysop($data);
		}
		return self::listSysops($data);
	}

	private static function sysop($data) {
		$page  = self::pw('page');
		$sysop = self::getSysop()->code(self::SYSTEM, $data->sysop);
		$page->headline = "ROPTM: $data->sysop Optional Codes";

		$filter = self::getFilterSysopOptions($data->sysop);
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::getRoptm()->recordlocker->deleteLock();

		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/roptm/sysop/edit/js.twig', ['roptm' => self::getRoptm()]);
		$html = self::displaySysop($data, $sysop, $codes);
		self::getRoptm()->deleteResponse();
		return $html;
	}

	private static function listSysops($data) {
		self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		self::getSysop()->recordlocker->deleteLock();

		$filter = self::getFilterSysop();

		if ($data->q) {
			$page->headline = "ROPTM: Searching Sysop '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/roptm/list/.js.twig');
		$html = self::displaySysopList($data, $codes);
		self::getRoptm()->deleteResponse();
		return $html;
	}

/* =============================================================
	CRUD
============================================================= */
	public static function handleCRUD($data) {
		$fields = ['action|text', 'sysop|text', 'code|text'];
		self::sanitizeParameters($data, $fields);
		$url = self::url();

		if ($data->action) {
			self::getRoptm()->processInput(self::pw('input'));

			switch ($data->action) {
				case 'update':
					$url = self::sysopFocusUrl($data->sysop, $data->code);
					break;
				default:
					$url = self::sysopUrl($data->sysop);
					break;
			}
		}

		self::pw('session')->redirect($url, $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySysop($data, MsaSysopCode $sysop, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('mar/armain/roptm/sysop/display.twig', ['roptm' => self::getRoptm(), 'sysop' => $sysop, 'codes' => $codes]);
		return $html;
	}

	private static function displaySysopList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('mar/armain/roptm/list/page.twig', ['sysopM' => self::getSysop(), 'roptm' => self::getRoptm(), 'codes' => $codes]);
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getRoptm()->getResponse();

		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}


/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getRoptm() {
		return Codes\Mar\Roptm::getInstance();
	}

	public static function getSysop() {
		return Codes\Msa\Sysop::getInstance();
	}

	public static function getFilterSysop() {
		$filter = new Filters\Msa\MsaSysopCode();
		$filter->system(self::SYSTEM);
		return $filter;
	}

	public static function getFilterSysopOptions($sysop = '') {
		$filter = new Filters\Msa\SysopOptionalCode();
		$filter->system(self::SYSTEM);

		if ($sysop) {
			$filter->query->filterBySysop($sysop);
		}
		return $filter;
	}

/* =============================================================
	URLs
============================================================= */
	public static function sysopUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('sysop', $id);
		return $url->getUrl();
	}

	public function sysopFocusUrl($sysop, $focus) {
		if (empty($focus) || self::getRoptm()->exists($sysop, $focus) === false) {
			return self::sysopUrl($sysop);
		}
		$filter   = self::getFilterSysopOptions($sysop);
		$position = $filter->positionQuick($focus);

		$url = new Purl(self::sysopUrl($sysop));
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'roptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($sysop, $code) {
		$url = new Purl(self::sysopUrl($sysop));
		$url->query->set('action', 'delete');
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function url() {
		return self::pw('pages')->get('pw_template=roptm')->url;
	}

	public static function urlFocus($focus = '') {
		$sysopM = self::getSysop();

		if (empty($focus) || $sysopM->exists(self::SYSTEM, $focus) === false) {
			return self::url();
		}
		$sysop = $sysopM->code(self::SYSTEM, $focus);
		$filter   = self::getFilterSysop();
		$position = $filter->positionQuick($sysop);
		$url = new Purl(self::url());
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'roptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=roptm)::sysopUrl', function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=roptm)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=roptm)::roptmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});
	}
}
