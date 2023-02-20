<?php namespace Controllers\Map\Apmain;
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
use Dplus\Codes;
// Controllers
use Controllers\Map\Apmain\AbstractController;


abstract class AbstractCodeTableController extends AbstractController {
	const DPLUSPERMISSION = 'apmain';
	const TITLE 		  = '';
	const SUMMARY		  = '';
	const SHOWONPAGE      = 10;
	const USE_EDIT_MODAL  = true;
	const USE_EDIT_PAGE   = false;


/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = static::TITLE;

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return static::handleCRUD($data);
		}
		if (static::USE_EDIT_PAGE && empty($data->code) === false) {
			return static::code($data);
		}
		return static::list($data);
	}

	public static function handleCRUD(WireData $data) {
		$fields = ['code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::url();
		$codeTable = static::getCodeTable();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}

		if ($data->action) {
			$codeTable->processInput(self::pw('input'));
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

		$codes = static::getCodeList($data);

		static::initHooks();
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/code-table.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/ajax-modal.js'));
		self::addVarsToJsVars($data);
		$page->js .= static::renderJs($data);
		$html = self::displayList($data, $codes);
		static::getCodeTable()->deleteResponse();
		return $html;
	}

/* =============================================================
	Data Fetching
============================================================= */
	protected static function getCodeList(WireData $data) {
		$filter = static::getCodeFilter();

		if (strlen($data->q) > 0) {
			$cols = self::pw('sanitizer')->array($data->col, ['delimiter' => ',']);
			$cols = empty(array_filter($cols)) ? ['code', 'description'] : $cols;
			$filter->search($data->q, $cols);
		}

		$filter->sortby(self::pw('page'));
		$input = self::pw('input');
		return $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayList(WireData $data, PropelModelPager $codes) {
		self::sanitizeParametersShort($data, ['print|bool']);
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= boolval($data->print) ? static::renderListForPrinting($data, $codes) : static::renderList($data, $codes);

		if (boolval($data->print) === false) {
			$html .= self::pw('config')->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}

		if (static::USE_EDIT_MODAL) {
			$html .= static::renderModal($data);
		}
		return $html;
	}

	protected static function displayCode(WireData $data, Code $code) {
		return '';
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderCode(WireData $data, Code $code) {
		return '';
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/edit-modal.twig', ['manager' => $codeTable]);
	}

	protected static function renderResponse(WireData $data) {
		$codeTable = static::getCodeTable();
		$response = $codeTable->getResponse();

		if (empty($response)) {
			return '';
		}
		if ($response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
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
		return self::pw('config')->twig->render('code-tables/bread-crumbs.twig');
	}

	protected static function renderJs(WireData $data) {
		$class = strtolower(static::getClassName());
		$config = self::pw('config');

		if ($config->twigloader->exists("code-tables/map/$class/.js.twig") === false) {
			return '';
		}
		return $config->twig->render("code-tables/map/$class/.js.twig", [$class => static::getCodeTable()]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url($code = '') {
		if (strlen($code) == 0) {
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
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

	public static function codeEditUrl($code) {
		$url = new Purl(self::url());
		$url->query->set('code', $code);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=apmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=apmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=apmain)::codeDeleteUrl', function($event) {
			$event->return = static::codeDeleteUrl($event->arguments(0));
		});

		if (static::USE_EDIT_PAGE) {
			$m->addHook('Page(pw_template=apmain)::codeListUrl', function($event) {
				$event->return = static::url($event->arguments(0));
			});
	
			$m->addHook('Page(pw_template=apmain)::codeAddUrl', function($event) {
				$event->return = self::codeEditUrl('new');
			});
	
			$m->addHook('Page(pw_template=apmain)::codeEditUrl', function($event) {
				$event->return = self::codeEditUrl($event->arguments(0));
			});
		}
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return CodeTable Manager
	 * @return Codes\AbstractCodeTable
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

	protected static function codeTableJsVarsArray(WireData $data) {
		return [
			'table' => strtolower(static::getClassName()),
			'config' => [
				'fields' => static::getCodeTableFieldConfigData($data),
				'urls' => [
					'api' => static::getCodeTableApiUrls($data)
				],
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
	 * Return Fields
	 * @return array
	 */
	protected static function getCodeTableFields() {
		return array_keys(static::getCodeTable()::FIELD_ATTRIBUTES);
	}

	/**
	 * Return URLs to JSON API
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @return array
	 */
	public static function getCodeTableApiUrls() {
		$class = strtolower(static::getClassName());
		
		return [
			'validate' => self::pw('page')->jsonApiUrl("map/validate/$class/code/"),
			'code'	   => self::pw('page')->jsonApiUrl("map/$class/code/"),
		];
	}
	
	/**
	 * Return Relative Path to JS directory for this CodeTable
	 * @return string
	 */
	protected static function getRelativeJsPath() {
		$class = strtolower(static::getClassName());
		return "scripts/code-tables/map/$class/";
	}

	/**
	 * Return relative Path to Requests JS class File
	 * @return string
	 */
	protected static function requestClassJsPath() {
		$jsPath = static::getRelativeJsPath();
		return $jsPath . 'classes/Requests.js';
	}

	/**
	 * Return relative Path to Form JS class File
	 * @return string
	 */
	protected static function formClassJsPath() {
		$jsPath = static::getRelativeJsPath();
		return $jsPath . 'classes/Form.js';
	}

	/**
	 * Return relative Path to Alerts JS class File
	 * @return string
	 */
	protected static function alertsClassJsPath() {
		$jsPath = static::getRelativeJsPath();
		return $jsPath . 'classes/Alerts.js';
	}

	/**
	 * Return JS filepath to custom JS file
	 * @return string
	 */
	protected static function codeTableCustomJsPath() {
		$class  = strtolower($class = static::getClassName());
		$jsPath = static::getRelativeJsPath();
		return $jsPath . "$class.js";
	}

	/**
	 * Append JS class files
	 * @param  WireData|null $data
	 * @return void
	 */
	protected static function appendJsClasses(WireData $data = null) {
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/classes/Requests.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/classes/Config.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/classes/Inputs.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/classes/Form.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/classes/Alerts.js'));

		
		$requestsJsPath = static::requestClassJsPath();

		if (file_exists(self::pw('config')->paths->templates . $requestsJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($requestsJsPath));
		}

		$formJsPath = static::formClassJsPath();

		if (file_exists(self::pw('config')->paths->templates . $formJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($formJsPath));
		}

		$alertsJsPath = static::alertsClassJsPath();

		if (file_exists(self::pw('config')->paths->templates . $alertsJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($alertsJsPath));
		}
	}

	
	/**
	 * Append JS files to loaded
	 * @param  WireData|null $data
	 * @return void
	 */
	protected static function appendJs(WireData $data = null) {
		static::appendJsClasses($data);

		if (static::USE_EDIT_MODAL) {
			// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-form.js'));
			// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/edit-modal-events.js'));
		}
		
		if (static::USE_EDIT_MODAL === false) { 
			// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/code-form.js'));
			// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/edit-code-events.js'));
		}

		$customJsPath = static::codeTableCustomJsPath();
		if (file_exists(self::pw('config')->paths->templates . $customJsPath)) {
			self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl($customJsPath));
		}
	}
}