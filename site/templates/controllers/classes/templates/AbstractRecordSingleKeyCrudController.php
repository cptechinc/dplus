<?php namespace Controllers\Templates;
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
use Controllers\Templates\AbstractController;


abstract class AbstractRecordSingleKeyController extends AbstractController {
	const DPLUSPERMISSION = '';
	const TITLE 		  = '';
	const SUMMARY		  = '';
	const SHOWONPAGE      = 10;
	const USE_EDIT_MODAL  = true;
	const USE_EDIT_PAGE   = false;

	const PARAMETERS_INDEX = ['id|text', 'action|text'];
	const SHOW_BREADCRUMBS = false;
	const PW_TEMPLATE = '';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		self::sanitizeParametersShort($data, static::PARAMETERS_INDEX);
		self::pw('page')->show_breadcrumbs = static::SHOW_BREADCRUMBS;

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return static::handleCRUD($data);
		}
		if (static::USE_EDIT_PAGE && empty($data->id) === false) {
			return static::record($data);
		}
		return static::list($data);
	}

	public static function handleCRUD(WireData $data) {
		self::sanitizeParametersShort($data, static::PARAMETERS_INDEX);
		$url  = self::url();
		$recordTable = static::getRecordTable();

		if (self::validateUserPermission() === false) {
			self::pw('session')->redirect(self::url(), $http301 = false);
		}

		if ($data->action) {
			$recordTable->processInput(self::pw('input'));
			$url = self::url($data->id);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	protected static function record(WireData $data) {
		return '';
	}

	protected static function list(WireData $data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page	= self::pw('page');
		$page->headline = static::TITLE;

		static::getClassName();

		$filter = static::getRecordFilter();

		if (strlen($data->q) > 0) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$ids = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);

		static::initHooks();
		// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/code-table.js'));
		// self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= static::renderListJs($data);
		$html = self::displayList($data, $ids);
		static::getRecordTable()->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayList(WireData $data, PropelModelPager $ids) {
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= static::renderList($data, $ids);

		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= self::pw('config')->twig->render('util/paginator/propel.twig', ['pager'=> $ids]);
		}

		if (static::USE_EDIT_MODAL) {
			$html .= static::renderModal($data);
		}
		return $html;
	}

	protected static function displayCode(WireData $data, Code $id) {
		return '';
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $records) {
		return '';
	}

	protected static function renderCode(WireData $data, Code $id) {
		return '';
	}

	protected static function renderModal(WireData $data) {
		return '';
	}

	protected static function renderResponse(WireData $data) {
		$recordTable = static::getRecordTable();
		$response = $recordTable->getResponse();

		if (empty($response) || $response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	protected static function renderLockedAlert($data) {
		$recordTable = static::getRecordTable();

		if ($recordTable->recordlocker->isLocked($data->id) === false || $recordTable->recordlocker->userHasLocked($data->id)) {
			return '';
		}

		$msg = "$data->id is being locked by " . $recordTable->recordlocker->getLockingUser($data->id);
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "$data->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
	}

	protected static function renderBreadcrumbs(WireData $data) {
		return '';
	}

	protected static function renderListJs(WireData $data) {
		return '';
	}

/* =============================================================
	URLs
============================================================= */
	public static function url($id = '') {
		if (empty($id)) {
			return static::_url();
		}
		return self::recordFocusUrl($id);
	}

	abstract public static function _url();

	public static function recordFocusUrl($focus) {
		$filter = static::getRecordFilter();

		if ($filter->exists($focus) === false) {
			return static::_url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, static::SHOWONPAGE);

		$url = new Purl(static::_url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, strtolower(static::getClassName()), $pagenbr);
		return $url->getUrl();
	}

	public static function recordDeleteUrl($id) {
		$url = new Purl(static::url());
		$url->query->set('code', $id);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

	public static function recordEditUrl($id) {
		$url = new Purl(self::url());
		$url->query->set('code', $id);
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page::menuUrl', function($event) {
			$menu = static::getMenuController();
			$event->return = $menu::url();
		});

		$m->addHook('Page::menuTitle', function($event) {
			$menu = static::getMenuController();
			$event->return = $menu::TITLE;
		});

		$m->addHook('Page::recordDeleteUrl', function($event) {
			$event->return = static::recordDeleteUrl($event->arguments(0));
		});

		if (static::USE_EDIT_PAGE) {
			$m->addHook('Page::recordListUrl', function($event) {
				$event->return = static::url($event->arguments(0));
			});
	
			$m->addHook('Page::recordAddUrl', function($event) {
				$event->return = self::recordEditUrl('new');
			});
	
			$m->addHook('Page::recordEditUrl', function($event) {
				$event->return = self::recordEditUrl($event->arguments(0));
			});
		}
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return CodeTable Manager
	 * @return WireData
	 */
	abstract public static function getRecordTable();

	/**
	 * Return Filter for CodeTable
	 * @return Filters\AbstractFilter
	 */
	abstract public static function getRecordFilter();

	/**
	 * Return Menu Class
	 * @return AbstractController
	 */
	abstract public static function getMenuController();

	/**
	 * Return this class's shortname
	 * @return string
	 */
	protected static function getClassName() {
		$reflector = new ReflectionClass(static::class);
		return $reflector->getShortName();
	}
}