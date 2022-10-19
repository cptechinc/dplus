<?php namespace Controllers\Map\Apmain;
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

class Aoptm extends AbstractController {
	const DPLUSPERMISSION = 'aoptm';
	const SYSTEM = 'AP';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['sysop|string', 'code|string', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		self::initHooks();

		if (empty($data->sysop) === false) {
			return self::sysop($data);
		}
		return self::listSysops($data);
	}

	private static function sysop($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page  = self::pw('page');
		$sysop = self::getSysop()->code(self::SYSTEM, $data->sysop);
		$page->headline = "AOPTM: $data->sysop Optional Codes";

		$filter = self::getFilterSysopOptions($data->sysop);
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::getAoptm()->recordlocker->deleteLock();

		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/sysop/edit/.js.twig', ['optm' => self::getAoptm()]);
		$html = self::displaySysop($data, $sysop, $codes);
		self::getAoptm()->deleteResponse();
		return $html;
	}

	private static function listSysops($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page = self::pw('page');
		$page->headline = "AP Optional Codes";
		self::getSysop()->recordlocker->deleteLock();

		$filter = self::getFilterSysop();

		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/list/.js.twig');
		$html = self::displaySysopList($data, $codes);
		self::getAoptm()->deleteResponse();
		return $html;
	}

/* =============================================================
	CRUD
============================================================= */
	public static function handleCRUD($data) {
		$fields = ['action|text', 'sysop|string', 'code|string'];
		self::sanitizeParameters($data, $fields);
		$url = self::url();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect($url, $http301 = false);
		}

		if ($data->action) {
			self::getAoptm()->processInput(self::pw('input'));

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
		$html .= $config->twig->render('code-tables/optm/sysop/display.twig', ['optm' => self::getAoptm(), 'sysop' => $sysop, 'codes' => $codes]);
		return $html;
	}

	private static function displaySysopList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('code-tables/optm/list/page.twig', ['sysopM' => self::getSysop(), 'optm' => self::getAoptm(), 'codes' => $codes]);
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getAoptm()->getResponse();

		if (empty($response)) {
			return '';
		}
		if ($response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}


/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getAoptm() {
		return Codes\Map\Aoptm::getInstance();
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
		if (empty($focus) || self::getAoptm()->exists($sysop, $focus) === false) {
			return self::sysopUrl($sysop);
		}
		$filter   = self::getFilterSysopOptions($sysop);
		$position = $filter->positionQuick($focus);

		$url = new Purl(self::sysopUrl($sysop));
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'aoptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
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
		return Menu::aoptmUrl();
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
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'aoptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=apmain)::sysopUrl', function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=apmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=apmain)::aoptmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});

		$m->addHook('Page(pw_template=apmain)::optmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});
	}
}
