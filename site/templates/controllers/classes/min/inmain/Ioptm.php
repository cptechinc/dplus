<?php namespace Controllers\Min\Inmain;
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use MsaSysopCode;
// ProcessWire Classes, Modules
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Validators
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;

class Ioptm extends AbstractController {
	const SYSTEM = 'IN';
	const DPLUSPERMISSION = 'ioptm';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}
		// Sanitize Params, parse route from params
		$fields = ['sysop|string', 'code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->sysop) === false) {
			return self::sysop($data);
		}
		return self::listSysops($data);
	}

	private static function sysop($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page  = self::pw('page');
		$sysop = self::getSysop()->code(self::SYSTEM, $data->sysop);
		$page->headline = "IOPTM: $data->sysop Optional Codes";

		$filter = self::getFilterSysopOptions($data->sysop);
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::getIoptm()->recordlocker->deleteLock();

		self::initHooks();
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/sysop/edit/.js.twig', ['optm' => self::getIoptm()]);
		$html = self::displaySysop($data, $sysop, $codes);
		self::getIoptm()->deleteResponse();
		return $html;
	}

	private static function listSysops($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page = self::pw('page');
		self::getSysop()->recordlocker->deleteLock();

		$filter = self::getFilterSysop();
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::initHooks();
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/list/.js.twig');
		$html = self::displaySysopList($data, $codes);
		self::addVarsToJsVars($data);
		self::getIoptm()->deleteResponse();
		return $html;
	}

/* =============================================================
	CRUD
============================================================= */
	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['action|text', 'sysop|string', 'code|string'];
		self::sanitizeParameters($data, $fields);
		$url = self::url();

		if ($data->action) {
			self::getIoptm()->processInput(self::pw('input'));

			switch ($data->action) {
				case 'update':
				case 'focus':
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
		$html .= $config->twig->render('code-tables/optm/sysop/display.twig', ['optm' => self::getIoptm(), 'sysop' => $sysop, 'codes' => $codes]);
		return $html;
	}

	private static function displaySysopList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('code-tables/optm/list/page.twig', ['sysopM' => self::getSysop(), 'optm' => self::getIoptm(), 'codes' => $codes]);
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getIoptm()->getResponse();

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
	public static function getIoptm() {
		return Codes\Min\Ioptm::getInstance();
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
	public static function url() {
		return Menu::ioptmUrl();
	}

	public static function sysopUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('sysop', $id);
		return $url->getUrl();
	}

	public function sysopFocusUrl($sysop, $focus) {
		if (empty($focus) || self::getIoptm()->exists($sysop, $focus) === false) {
			return self::sysopUrl($sysop);
		}
		$filter   = self::getFilterSysopOptions($sysop);
		$position = $filter->positionQuick($focus);

		$url = new Purl(self::sysopUrl($sysop));
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'ioptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($sysop, $code) {
		$url = new Purl(self::sysopUrl($sysop));
		$url->query->set('action', 'delete');
		$url->query->set('code', $code);
		return $url->getUrl();
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
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'ioptm', self::getPagenbrFromOffset($position, self::pw('session')->display));
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=inmain)::sysopUrl', function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::ioptmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::optmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Add Variables to JS Vars Array
	 * @param  WireData $data
	 * @return void
	 */
	protected static function addVarsToJsVars(WireData $data) {
		$table = 'ioptm';
		$jsVars = self::pw('config')->js('vars');
		$jsVars['codetable'] = [
			'table' => strtolower($table),
		];
		self::pw('config')->js('vars', $jsVars);
	}
}
