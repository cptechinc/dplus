<?php namespace Controllers\Mar\Armain;
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

class Roptm extends AbstractController {
	const DPLUSPERMISSION = 'roptm';
	const SYSTEM = 'AR';

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

		if (empty($data->sysop) === false) {
			return self::sysop($data);
		}
		return self::listSysops($data);
	}

	private static function sysop($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page  = self::pw('page');
		$sysop = self::getSysop()->code(self::SYSTEM, $data->sysop);
		$page->headline = "AR Optional Code: $data->sysop";

		$filter = self::getFilterSysopOptions($data->sysop);
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		self::getRoptm()->recordlocker->deleteLock();
		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/sysop/edit/.js.twig', ['optm' => self::getRoptm()]);
		$html = self::displaySysop($data, $sysop, $codes);
		self::addVarsToJsVars($data);
		self::getRoptm()->deleteResponse();
		return $html;
	}

	private static function listSysops($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page = self::pw('page');
		$page->headline = "AR Optional Codes";
		self::getSysop()->recordlocker->deleteLock();

		$filter = self::getFilterSysop();

		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$filter->sortby($page);
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/optm/list/.js.twig');
		$html = self::displaySysopList($data, $codes);
		self::getRoptm()->deleteResponse();
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
			self::getRoptm()->processInput(self::pw('input'));

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
		$html .= $config->twig->render('code-tables/optm/sysop/display.twig', ['optm' => self::getRoptm(), 'sysop' => $sysop, 'codes' => $codes]);
		return $html;
	}

	private static function displaySysopList($data, PropelModelPager $codes) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('code-tables/optm/list/page.twig', ['sysopM' => self::getSysop(), 'optm' => self::getRoptm(), 'codes' => $codes]);
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getRoptm()->getResponse();

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
	public static function url() {
		$url = new Purl(Menu::url());
		$url->path->add('roptm');
		return $url->getUrl();
	}

	public static function sysopUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('sysop', $id);
		return $url->getUrl();
	}

	public function sysopFocusUrl($sysop, $focus) {
		if (strlen($focus) == 0 || self::getRoptm()->exists($sysop, $focus) === false) {
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

	public static function urlFocus($focus = '') {
		$sysopM = self::getSysop();

		if (strlen($focus) == 0 || $sysopM->exists(self::SYSTEM, $focus) === false) {
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

		$m->addHook('Page(pw_template=armain)::sysopUrl', function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=armain)::roptmUrl', function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::optmUrl', function($event) {
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
		$table = 'roptm';
		$jsVars = self::pw('config')->js('vars');
		$jsVars['codetable'] = [
			'table' => strtolower($table),
		];
		self::pw('config')->js('vars', $jsVars);
	}
}
