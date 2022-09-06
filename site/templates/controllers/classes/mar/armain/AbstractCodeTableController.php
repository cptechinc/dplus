<?php namespace Controllers\Mar\Armain;
// Purl URI manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;
// Controllers
use Controllers\Mar\AbstractController;
use ReflectionClass;

abstract class AbstractCodeTableController extends AbstractController {
	const DPLUSPERMISSION = 'armain';
	const TITLE 		  = '';
	const SUMMARY		  = '';
	const SHOWONPAGE = 10;

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = static::TITLE;

		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD(WireData $data) {
		$fields = ['code|text', 'action|text'];
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

	private static function list(WireData $data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);
		$page	= self::pw('page');
		$page->headline = static::TITLE;

		static::getClassName();

		$filter = static::getCodeFilter();

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$codes = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : static::SHOWONPAGE);

		self::initHooks();
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/code-table.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/code-tables/modal-events.js'));
		$page->js .= static::renderJs($data);
		$html = self::displayList($data, $codes);
		static::getCodeTable()->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayList(WireData $data, PropelModelPager $codes) {
		$html  = '';
		$html .= static::renderBreadcrumbs($data);
		$html .= static::renderResponse($data);
		$html .= static::renderList($data, $codes);

		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= self::pw('config')->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		}
		$html .= static::renderModal($data);
		return $html;
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/edit-modal.twig', ['manager' => $codeTable]);
	}

	public static function renderResponse(WireData $data) {
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

	public static function renderBreadcrumbs(WireData $data) {
		return self::pw('config')->twig->render('code-tables/bread-crumbs.twig');
	}

	protected static function renderJs(WireData $data) {
		$class = strtolower(static::getClassName());
		return self::pw('config')->twig->render("code-tables/mar/$class/.js.twig", [$class => static::getCodeTable()]);
	}

/* =============================================================
	URLs
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
			return Menu::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, static::SHOWONPAGE);

		$url = new Purl(static::_url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'ccm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(static::_url());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=armain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=armain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = static::codeDeleteUrl($event->arguments(0));
		});
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
}