<?php namespace Controllers\Abstracts;
// Base PHP
use ReflectionClass;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// Dplus Models
use MsaSysopCode;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;
// App
use App\Urls\PurlPaginator;
// Controllers
use Controllers\AbstractController;

/**
 * Roptm
 * 
 * Handles AR (Accounts Receivable) Optional Code Requests
 */
abstract class AbstractCodeTableOptmController extends AbstractController {
	const DPLUSPERMISSION = 'roptm';
	const TITLE      = 'AR Optional Codes';
	const SUMMARY    = 'View / Edit AR Optional Codes';
	const SYSTEM = 'AR';
	const OPTM   = 'ROPTM';
	const SHOWONPAGE = 10;
	const TEMPLATE   = 'armain';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['sysop|string', 'code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return self::process($data);
		}
		
		if (empty($data->sysop) === false) {
			return self::sysop($data);
		}
		return self::listSysops($data);
	}

	public static function process(WireData $data) {
		$fields = ['action|text', 'sysop|string', 'code|string'];
		self::sanitizeParameters($data, $fields);
		$url = static::url();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect($url, $http301 = false);
		}

		if ($data->action) {
			static::getOptm()->process(self::pw('input'));

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

	protected static function listSysops($data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$page = self::pw('page');
		$page->headline = static::SYSTEM . " Optional Codes";
		self::getSysop()->recordlocker->deleteLock();

		if ($data->has('print') === false) {
			$page->breadcrumbs = static::renderBreadcrumbs($data);
		}
		static::initHooks();
		$html = self::displaySysopList($data, static::getSysopsList($data));
		static::getOptm()->deleteResponse();
		return $html;
	}

	protected static function sysop(WireData $data) {
		self::sanitizeParametersShort($data, ['q|text', 'col|text']);
		$sysopTable   = self::getSysop();
		$optionsTable = static::getOptm();
		$optionsTable->recordlocker->deleteLock();

		if ($sysopTable->exists(static::SYSTEM, $data->sysop) === false) {
			self::pw('session')->redirect(static::url(), $http301=false);
		}
		
		$page  = self::pw('page');
		$page->headline = static::OPTM.": $data->sysop Optional Codes";
		$sysop = $sysopTable->code(static::SYSTEM, $data->sysop);
		$sysopOptions = static::getSysopOptionsList($data);
		
		self::initHooks();
		if ($data->has('print') === false) {
			$page->breadcrumbs = static::renderBreadcrumbs($data);
		}
		$html = self::displaySysop($data, $sysop, $sysopOptions);
		$optionsTable->deleteResponse();
		if ($data->has('print') === false) {
			self::addVarsToJsVars($data);
			self::appendEditJs();
		}
		return $html;
	}


/* =============================================================
	2. Validations / Permissions / Initializations
============================================================= */

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */
	/**
	 * Return filtered List of System Options
	 * @param  WireData $data
	 * @return PropelModelPager
	 */
	protected static function getSysopsList(WireData $data) {
		$filter = self::getSysopFilter();
		/** @var WireInput */
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		/** @var WireInput */
		$input = self::pw('input');
		$filter->sort($input->get);
		return $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);
	}

	/**
	 * Return Filtered List of Code Options
	 *
	 * @param  WireData $data
	 * @return PropelModelPager
	 */
	protected static function getSysopOptionsList(WireData $data) {
		$filter = self::getSysopOptionsFilter($data->sysop);
		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$input = self::pw('input');
		$filter->sort($input->get);
		return $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);
	}

/* =============================================================
	4. URLs
============================================================= */
	abstract public static function url();

	public static function sysopUrl($id) {
		$url = new Purl(static::url());
		$url->query->set('sysop', $id);
		return $url->getUrl();
	}

	public function sysopFocusUrl($sysop, $focus) {
		if (empty($focus) || static::getOptm()->exists($sysop, $focus) === false) {
			return self::sysopUrl($sysop);
		}
		$filter   = self::getSysopOptionsFilter($sysop);
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, static::SHOWONPAGE);

		$url = new Purl(self::sysopUrl($sysop));
		$url = PurlPaginator::paginate($url, strtolower(static::getClassName()), $pagenbr);
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

		if (empty($focus) || $sysopM->exists(static::SYSTEM, $focus) === false) {
			return static::url();
		}
		$sysop = $sysopM->code(static::SYSTEM, $focus);
		$filter   = self::getSysopFilter();
		$position = $filter->positionQuick($sysop);
		$pagenbr = self::getPagenbrFromOffset($position, static::SHOWONPAGE);
		$url = new Purl(static::url());
		$url = PurlPaginator::paginate($url, strtolower(static::getClassName()), $pagenbr);
		$url->query->set('focus', $focus);
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displaySysopList(WireData $data, PropelModelPager $sysOptions) {
		if ($data->has('print')) {
			return static::renderSysopListPrint($data, $sysOptions);
		}
		return static::renderSysopList($data, $sysOptions);
	}

	protected static function displaySysop(WireData $data, MsaSysopCode $sysop, PropelModelPager $sysopOptions) {
		if ($data->has('print')) {
			return static::renderSysopPrint($data, $sysop, $sysopOptions);
		}
		return static::renderSysop($data, $sysop, $sysopOptions);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('codes/optm/bread-crumbs.twig');
	}

	protected static function renderSysopList(WireData $data, PropelModelPager $sysOptions) {
		return self::pw('config')->twig->render('codes/optm/list/display.twig', ['SYSOP' => self::getSysop(), 'sysOptions' => $sysOptions]);
	}

	protected static function renderSysopListPrint(WireData $data, PropelModelPager $sysOptions) {
		return self::pw('config')->twig->render('codes/optm/list/display-print.twig', ['SYSOP' => self::getSysop(), 'sysOptions' => $sysOptions]);
	}

	protected static function renderSysop(WireData $data, MsaSysopCode $sysop, PropelModelPager $sysopOptions) {
		return self::pw('config')->twig->render('codes/optm/sysop/display.twig', ['SYSOP' => self::getSysop(), 'OPTM' => static::getOptm(), 'sysop' => $sysop, 'sysopOptions' => $sysopOptions]);
	}

	protected static function renderSysopPrint(WireData $data, MsaSysopCode $sysop, PropelModelPager $sysopOptions) {
		// return self::pw('config')->twig->render('codes/optm/list/display-print.twig', ['SYSOP' => self::getSysop(), 'OPTM' => static::getOptm(), 'sysop' => $sysop,'sysOptions' => $sysOptions]);
	}

/* =============================================================
	7. Class / Module Getters
============================================================= */
	/**
	 * Return Filter for System Optional Codes
	 * @return Filters\Msa\MsaSysopCode
	 */
	public static function getSysopFilter() {
		$filter = new Filters\Msa\MsaSysopCode();
		$filter->system(static::SYSTEM);
		return $filter;
	}

	/**
	* Return Filter for Code Options
	* @return Filters\Msa\SysopOptionalCode
	*/
	public static function getSysopOptionsFilter($sysop = '') {
		$filter = new Filters\Msa\SysopOptionalCode();
		$filter->system(static::SYSTEM);

		if ($sysop) {
			$filter->query->filterBySysop($sysop);
		}
		return $filter;
	}

	/**
	 * Return Sysop Manager
	 * @return Codes\Msa\Sysop
	 */
	public static function getSysop() {
		return Codes\Msa\Sysop::instance();
	}

	/**
	 * Return Optional Code Options Manager
	 * @return Codes\AbstractOptm
	 */
	abstract public static function getOptm();

	/**
	 * Return this class's shortname
	 * @return string
	 */
	protected static function getClassName() {
		$reflector = new ReflectionClass(static::class);
		return $reflector->getShortName();
	}

/* =============================================================
	8. Supplemental
============================================================= */
	/**
	 * Add Variables to JS Vars Array
	 * @param  WireData $data
	 * @return void
	 */
	protected static function addVarsToJsVars(WireData $data) {
		$jsVars = self::pw('config')->js('vars');
		$jsVars['codetable'] = [
			'code' => static::getOptm()::RECORDLOCKER_FUNCTION,
			'config' => [
				'system' => static::SYSTEM,
				'fields' => static::getOptmFieldConfigData($data),
				'urls' => [
					'api' => static::getOptmApiUrls($data)
				]
			],
			'fields' => static::getOptmFields()
		];
		self::pw('config')->js('vars', $jsVars);
	}

	/**
	 * Return Optm field Config Data
	 * @param  WireData $data
	 * @return array
	 */
	protected static function getOptmFieldConfigData(WireData $data) {
		$table = static::getOptm();
		return [
			'code'		  => ['maxlength' => $table->fieldAttribute('code', 'maxlength')],
			'description' => ['maxlength' => $table->fieldAttribute('description', 'maxlength')],
		];
	}

	/**
	 * Return URLs to JSON API
	 * @return array
	 */
	protected static function getOptmApiUrls() {
		$menu = 'm'.strtolower(static::SYSTEM);
		$optm = strtolower(static::OPTM);

		return [
			'validate' => self::pw('page')->jsonApiUrl("$menu/validate/$optm/code/"),
			'code'	   => self::pw('page')->jsonApiUrl("$menu/$optm/code/"),
		];
	}

	/**
	 * Return Fields
	 * @return array
	 */
	protected static function getOptmFields() {
		return array_keys(static::getOptm()::FIELD_ATTRIBUTES);
	}

	protected static function appendEditJsClasses(WireData $data = null) {
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Config.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Inputs.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Form.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Alerts.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/optm/classes/Requests.js'));
	}

	protected static function appendEditJs(WireData $data = null) {
		static::appendEditJsClasses($data);
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/optm/edit-modal-events.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/optm/optm.js'));
	}

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$template = static::TEMPLATE;

		$m = self::pw('modules')->get('Dplus');

		$m->addHookProperty("Page(pw_template=$template)::system", function($event) {
			$event->return = static::SYSTEM;
		});

		$m->addHook("Page(pw_template=$template)::sysopUrl", function($event) {
			$event->return = self::sysopUrl($event->arguments(0));
		});

		$m->addHook("Page(pw_template=$template)::codeDeleteUrl", function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook("Page(pw_template=$template)::optmUrl", function($event) {
			$event->return = self::urlFocus($event->arguments(0));
		});
	}

/* =============================================================
	10. Sessions
============================================================= */
}
