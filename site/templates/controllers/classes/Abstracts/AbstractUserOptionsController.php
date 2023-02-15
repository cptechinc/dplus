<?php namespace Controllers\Abstracts;
// Base PHP
use ReflectionClass;
// Purl URI manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\UserOptions;
use Dplus\UserOptions\AbstractManager;
// Controllers
use Controllers\AbstractController;

/**
 * AbstractUserOptionsController
 * 
 * Base Class for rendering User Options
 */
abstract class AbstractUserOptionsController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE 		  = '';
	const SUMMARY		  = '';
	const SHOWONPAGE	  = 10;
	const BASE_MENU_CODE  = '';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['userID|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->headline = static::TITLE;

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return static::process($data);
		}
		if (strlen($data->userID) > 0) {
			return static::user($data);
		}
		return static::list($data);
	}

	public static function process(WireData $data) {
		$fields = ['userID|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$table = static::getManager();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}

		if ($data->action) {
			$table->processInput(self::pw('input'));
			$url = self::url($data->userID);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function user(WireData $data) {
		$table = static::getManager();
		$user  = $table->userOrNew($data->userID);

		if ($user->isNew() === false) {
			self::pw('page')->headline = "Editing $user->userid";
			$table->lockrecord($user);
		}
		self::initHooks();
		self::appendJs();
		self::addVarsToJsVars($data);
		$html = static::displayUser($data, $user);
		$table->deleteResponse();
		return $html;
	}

	protected static function list(WireData $data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page	= self::pw('page');
		$page->headline = static::TITLE;
		static::getManager()->recordlocker->deleteLock();

		$users = static::getUserList($data);
		static::initHooks();

		$html = self::displayList($data, $users);
		static::getManager()->deleteResponse();
		return $html;
	}

/* =============================================================
	2. Validations
============================================================= */

/* =============================================================
	3. Data Requests / Data Fetching
============================================================= */
	/**
	 * Return filtered List of Codes
	 * @param  WireData $data
	 * @return PropelModelPager
	 */
	protected static function getUserList(WireData $data) {
		$filter = static::getUserFilter();
		$filter->userid(static::getManager()->userids());

		if (strlen($data->q) > 0) {
			$cols = self::pw('sanitizer')->array($data->col, ['delimiter' => ',']);
			$cols = empty(array_filter($cols)) ? ['id', 'name'] : $cols;
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
	public static function url($userID = '') {
		if (empty($userID)) {
			return static::_url();
		}
		return self::userFocusUrl($userID);
	}

	abstract public static function _url();

	public static function userFocusUrl($focus) {
		$table = static::getManager();
		$filter = static::getUserFilter();
		$filter->query->filterByUserid($table->userIDs());

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

	public static function userDeleteUrl($userID) {
		$url = new Purl(static::_url());
		$url->query->set('userID', $userID);
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

	public static function userEditUrl($userID) {
		$url = new Purl(self::url());
		$url->query->set('userID', $userID);
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
	protected static function displayList(WireData $data, PropelModelPager $users) {
		self::sanitizeParametersShort($data, ['print|bool']);
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= boolval($data->print) ? static::renderListForPrinting($data, $users) : static::renderList($data, $users);
		return $html;
	}

	protected static function displayUser(WireData $data, Code $user) {
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= static::renderUser($data, $user);
		return $html;
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $users) {
		$table = static::getManager();
		return self::pw('config')->twig->render('user-options/list/display.twig', ['manager' => $table, 'users' => $users]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $users) {
		$table = static::getManager();
		return self::pw('config')->twig->render('codes/list/display-print.twig', ['manager' => $table, 'codes' => $users]);
	}

	protected static function renderUser(WireData $data, Code $user) {
		$table = static::getManager();
		return self::pw('config')->twig->render('user-options/user/display.twig', ['manager' => $table, 'u' => $user]);
	}

	protected static function renderResponse(WireData $data) {
		$table = static::getManager();
		$response = $table->getResponse();

		if (empty($response)) {
			return '';
		}
		if ($response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('codes/response.twig', ['response' => $response]);
	}

	protected static function renderLockedAlert($data) {
		$table = static::getManager();

		if ($table->recordlocker->isLocked($data->userID) === false || $table->recordlocker->userHasLocked($data->userID)) {
			return '';
		}

		$msg = "User $data->userID is being locked by " . $table->recordlocker->getLockingUser($data->userID);
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Code $data->userID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
	}

	protected static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('user-options/bread-crumbs.twig');
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	/**
	 * Return Manager
	 * @return UserOptions\AbstractManager
	 */
	abstract public static function getManager();

	/**
	 * Return User Filter
	 * @return Filters\Msa\DplusUser
	 */
	public static function getUserFilter() {
		return new Filters\Msa\DplusUser();
	}

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
	protected static function jsVarsArray(WireData $data) {
		return [
			'code'   => static::getManager()::RECORDLOCKER_FUNCTION,
			'config' => [
				'fields' => static::getFieldsConfigData($data),
				'urls' => [
					'api' => static::getApiUrls($data)
				]
			],
			'fields' => static::getUserOptionsFields()
		];
	}

	/**
	 * Add Variables to JS Vars Array
	 * @param  WireData $data
	 * @return void
	 */
	protected static function addVarsToJsVars(WireData $data) {
		$jsVars = self::pw('config')->js('vars');
		$jsVars['useroptions'] = static::jsVarsArray($data);
		self::pw('config')->js('vars', $jsVars);
	}

	/**
	 * Return Fields
	 * @return array
	 */
	protected static function getUserOptionsFields() {
		return array_keys(static::getManager()::FIELD_ATTRIBUTES);
	}

	/**
	 * Return CodeTable field Config Data
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @param  WireData $data
	 * @return array
	 */
	public static function getFieldsConfigData(WireData $data) {
		$table = static::getManager();
		return [
			// 'userID'		  => ['maxlength' => $table->fieldAttribute('userID', 'maxlength')],
			// 'description' => ['maxlength' => $table->fieldAttribute('description', 'maxlength')],
		];
	}

	/**
	 * Return URLs to JSON API
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @return array
	 */
	public static function getApiUrls() {
		$menu = static::BASE_MENU_CODE;
		$inspector = static::getStaticReflectionClass();
		$class = strtolower($inspector->getShortName());
		
		return [
			'validate' => self::pw('page')->jsonApiUrl("$menu/validate/$class/userid/"),
			'user'     => self::pw('page')->jsonApiUrl("$menu/$class/user/"),
		];
	}

	/**
	 * Return Relative Path to JS directory for this CodeTable
	 * @return string
	 */
	protected static function getRelativeJsPath() {
		$inspector = static::getStaticReflectionClass();
		$class     = strtolower($inspector->getShortName());
		return "scripts/user-options/$class/";
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
	protected static function customJsPath() {
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

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/validate-form.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/events.js'));

		$customJsPath = static::customJsPath();
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
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/ajax-modal.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/classes/Requests.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/classes/Config.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/classes/Inputs.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/classes/Form.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/user-options/classes/Alerts.js'));

		
		// $requestsJsPath = static::requestClassJsPath();

		// if (file_exists(self::pw('config')->paths->templates . $requestsJsPath)) {
		// 	self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($requestsJsPath));
		// }

		// $formJsPath = static::formClassJsPath();

		// if (file_exists(self::pw('config')->paths->templates . $formJsPath)) {
		// 	self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($formJsPath));
		// }
	}
	
/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$template = self::pw('page')->pw_template;

		$m = self::pw('modules')->get('Dpages');

		$m->addHook("Page(pw_template=$template)::menuUrl", function($event) {
			$event->return = static::menuUrl();
		});

		$m->addHook("Page(pw_template=$template)::menuTitle", function($event) {
			$event->return = static::menuTitle();
		});

		$m->addHook("Page(pw_template=$template)::userDeleteUrl", function($event) {
			$event->return = static::userDeleteUrl($event->arguments(0));
		});

		$m->addHook("Page(pw_template=$template)::userListUrl", function($event) {
			$event->return = self::url($event->arguments(0));
		});

		$m->addHook("Page(pw_template=$template)::userAddUrl", function($event) {
			$event->return = self::userEditUrl('new');
		});

		$m->addHook("Page(pw_template=$template)::userEditUrl", function($event) {
			$event->return = self::userEditUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	
}