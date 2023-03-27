<?php namespace Controllers\Abstracts;
// Base PHP
use ReflectionClass;
// Purl URI manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\User;
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;
// Controllers
use Controllers\AbstractController;

/**
 * AbstractCodeTableController
 * 
 * Base Class for rendering Code Tables
 */
abstract class AbstractCodeTableController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE 		  = '';
	const SUMMARY		  = '';
	const SHOWONPAGE	  = 10;
	const USE_EDIT_MODAL  = true;
	const USE_EDIT_PAGE   = false;
	const BASE_MENU_CODE  = '';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->headline = static::TITLE;

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return static::process($data);
		}
		if (static::USE_EDIT_PAGE && strlen($data->code) > 0) {
			return static::code($data);
		}
		return static::list($data);
	}

	public static function process(WireData $data) {
		$fields = ['code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$codeTable = static::getCodeTable();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}

		if ($data->action) {
			$codeTable->process(self::pw('input'));
			$url = self::url($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function code(WireData $data) {
		return '';
	}

	protected static function list(WireData $data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page	= self::pw('page');
		$page->headline = static::TITLE;
		static::getCodeTable()->recordlocker->deleteLock();

		$codes = static::getCodesList($data);
		static::initHooks();

		if ($data->has('print') === false) {
			if (static::USE_EDIT_MODAL) {
				static::addVarsToJsVars($data);
				static::appendJs();	
			}
			$page->breadcrumbs = static::renderBreadcrumbs($data);
		}
		$html = self::displayList($data, $codes);
		static::getCodeTable()->deleteResponse();
		return $html;
	}

/* =============================================================
	2. Validations
============================================================= */
	/**
	 * Return if User has Permission To Menu
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (static::validateUserMenuPermission($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	/**
	 * Return if User has Permission To Menu
	 * @param  User|null $user
	 * @return bool
	 */
	abstract public static function validateUserMenuPermission(User $user = null);

/* =============================================================
	3. Data Requests / Data Fetching
============================================================= */
	/**
	 * Return filtered List of Codes
	 * @param  WireData $data
	 * @return PropelModelPager
	 */
	protected static function getCodesList(WireData $data) {
		$filter = static::getCodeFilter();

		if (strlen($data->q) > 0) {
			$cols = self::pw('sanitizer')->array($data->col, ['delimiter' => ',']);
			$cols = empty(array_filter($cols)) ? ['code', 'description'] : $cols;
			$filter->search($data->q, $cols);
		}
		
		/** @var WireInput */
		$input = self::pw('input');
		$filter->sort($input->get);
		return $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function url($code = '') {
		if (empty($code)) {
			return static::_url();
		}
		return self::codeFocusUrl($code);
	}

	abstract public static function _url();

	public static function codeFocusUrl($focus) {
		$filter = static::getCodeFilter();

		if ($filter->exists($focus) === false) {
			return static::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, static::SHOWONPAGE);

		$url = new Purl(static::_url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, strtolower(static::getClassName()), $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(static::_url());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function codeEditUrl($code) {
		$url = new Purl(self::url());
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	/**
	 * Return URL to Menu Page
	 * @return string
	 */
	abstract public static function menuUrl();

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayList(WireData $data, PropelModelPager $codes) {
		self::sanitizeParametersShort($data, ['print|bool']);
		$html  = '';
		$html .= static::renderResponse($data);
		$html .= boolval($data->print) ? static::renderListForPrinting($data, $codes) : static::renderList($data, $codes);
		return $html;
	}

	protected static function displayCode(WireData $data, Code $code) {
		return '';
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('codes/list/display.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('codes/list/display-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderCode(WireData $data, Code $code) {
		return '';
	}

	protected static function renderResponse(WireData $data) {
		$codeTable = static::getCodeTable();
		$response = $codeTable->getResponse();

		if (empty($response) || $response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('codes/response.twig', ['response' => $response]);
	}

	protected static function renderLockedAlert($data) {
		$codeTable = static::getCodeTable();

		if ($codeTable->recordlocker->isLocked($data->code) === false || $codeTable->recordlocker->userHasLocked($data->code)) {
			return '';
		}

		$msg = "Code $data->code is being locked by " . $codeTable->recordlocker->getLockingUser($data->code);
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Code $data->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
	}

	protected static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('codes/bread-crumbs.twig');
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	/**
	 * Return CodeTable Manager
	 * @return Codes\AbstractCodeTableEditable 
	 */
	abstract public static function getCodeTable();

	/**
	 * Return Filter for CodeTable
	 * @return Filters\AbstractFilter
	 */
	abstract public static function getCodeFilter();

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
	 * Return Menu Page Title
	 * @return string
	 */
	abstract protected static function menuTitle();

	/**
	 * Return Reflection Class
	 * @return ReflectionClass
	 */
	protected static function getStaticReflectionClass() {
		return new ReflectionClass(static::class);
	}

	/**
	 * Return Array for JS vars for CodeTable
	 * @param  WireData $data
	 * @return array
	 */
	protected static function codeTableJsVarsArray(WireData $data) {
		return [
			'code'   => static::getCodeTable()::RECORDLOCKER_FUNCTION,
			'config' => [
				'fields' => static::getCodeTableFieldConfigData($data),
				'urls' => [
					'api' => static::getCodeTableApiUrls($data)
				]
			],
			'fields' => static::getCodeTableFields()
		];
	}

	/**
	 * Add Variables to JS Vars Array
	 * @param  WireData $data
	 * @return void
	 */
	protected static function addVarsToJsVars(WireData $data) {
		$jsVars = self::pw('config')->js('vars');
		$jsVars['codetable'] = static::codeTableJsVarsArray($data);
		self::pw('config')->js('vars', $jsVars);
	}

	/**
	 * Return Fields
	 * @return array
	 */
	protected static function getCodeTableFields() {
		return array_keys(static::getCodeTable()::FIELD_ATTRIBUTES);
	}

	/**
	 * Return CodeTable field Config Data
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @param  WireData $data
	 * @return array
	 */
	public static function getCodeTableFieldConfigData(WireData $data) {
		$table = static::getCodeTable();
		return [
			'code'		  => ['maxlength' => $table->fieldAttribute('code', 'maxlength')],
			'description' => ['maxlength' => $table->fieldAttribute('description', 'maxlength')],
		];
	}

	/**
	 * Return URLs to JSON API
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @return array
	 */
	public static function getCodeTableApiUrls() {
		$menu = static::BASE_MENU_CODE;
		$inspector = static::getStaticReflectionClass();
		$class = strtolower($inspector->getShortName());
		
		return [
			'validate' => self::pw('page')->jsonApiUrl("$menu/validate/$class/code/"),
			'code'	   => self::pw('page')->jsonApiUrl("$menu/$class/code/"),
		];
	}

	/**
	 * Return Relative Path to JS directory for this CodeTable
	 * @return string
	 */
	protected static function getRelativeJsPath() {
		$menu      = static::BASE_MENU_CODE;
		$inspector = static::getStaticReflectionClass();
		$class     = strtolower($inspector->getShortName());
		return "scripts/codes/$menu/$class/";
	}

	/**
	 * Return relative Path to Request JS class File
	 * @return string
	 */
	protected static function requestClassJsPath() {
		$jsPath = static::getRelativeJsPath();
		return $jsPath . 'classes/Requests.js';
	}

	/**
	 * Return relative Path to Request JS class File
	 * @return string
	 */
	protected static function formClassJsPath() {
		$jsPath = static::getRelativeJsPath();
		return $jsPath . 'classes/Form.js';
	}

	/**
	 * Return JS filepath to custom JS file
	 * @return string
	 */
	protected static function codeTableCustomJsPath() {
		$inspector = static::getStaticReflectionClass();
		$class     = strtolower($inspector->getShortName());
		$jsPath    = static::getRelativeJsPath();
		return $jsPath . "$class.js";
	}

	/**
	 * Append JS files to loaded
	 * @param  WireData|null $data
	 * @return void
	 */
	protected static function appendJs(WireData $data = null) {
		static::appendJsClasses($data);

		if (static::USE_EDIT_MODAL) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/modal-form.js'));
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/edit-modal-events.js'));
		}
		
		if (static::USE_EDIT_MODAL === false) { 
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/code-form.js'));
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/edit-code-events.js'));
		}

		$customJsPath = static::codeTableCustomJsPath();
		if (file_exists(self::pw('config')->paths->templates . $customJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($customJsPath));
		}
	}

	/**
	 * Append JS class files
	 * @param  WireData|null $data
	 * @return void
	 */
	protected static function appendJsClasses(WireData $data = null) {
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Requests.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Config.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Inputs.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Form.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/codes/classes/Alerts.js'));

		
		$requestsJsPath = static::requestClassJsPath();

		if (file_exists(self::pw('config')->paths->templates . $requestsJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($requestsJsPath));
		}

		$formJsPath = static::formClassJsPath();

		if (file_exists(self::pw('config')->paths->templates . $formJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($formJsPath));
		}
	}
	
/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$template = self::pw('page')->pw_template;

		$m = self::pw('modules')->get('Dplus');


		$m->addHook("Page(pw_template=$template)::menuUrl", function($event) {
			$event->return = static::menuUrl();
		});

		$m->addHook("Page(pw_template=$template)::menuTitle", function($event) {
			$event->return = static::menuTitle();
		});

		$m->addHook("Page(pw_template=$template)::codeDeleteUrl", function($event) {
			$event->return = static::codeDeleteUrl($event->arguments(0));
		});

		if (static::USE_EDIT_PAGE) {
			$m->addHook("Page(pw_template=$template)::codeListUrl", function($event) {
				$event->return = self::url($event->arguments(0));
			});
	
			$m->addHook("Page(pw_template=$template)::codeAddUrl", function($event) {
				$event->return = self::codeEditUrl('new');
			});
	
			$m->addHook("Page(pw_template=$template)::codeEditUrl", function($event) {
				$event->return = self::codeEditUrl($event->arguments(0));
			});
		}
	}

/* =============================================================
	Supplemental
============================================================= */
	
}