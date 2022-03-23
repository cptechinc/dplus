<?php namespace Controllers\Mar\Armain;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use Customer;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Mar\Armain\Cmm as CmmManager;
// Mvc Controllers
use Controllers\Mar\Armain\Base;

class Cmm extends Base {
	const DPLUSPERMISSION = 'cmm';
	const SHOWONPAGE = 20;

	private static $cmm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::displayAlertUserPermission($data);
		}
		// Sanitize Params, parse route from params
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->id) === false) {
			return self::customer($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['id|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::cmmUrl();
		$cmm = self::getCmm();

		if ($data->action) {
			$cmm->processInput(self::pw('input'));

			if ($data->action != 'delete') {
				$url = self::custEditUrl($data->id);
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::getCmm()->recordlocker->deleteLock();
		$page   = self::pw('page');
		$page->headline = "Customer Maintenance";

		$filter = new Filters\Mar\Customer();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "CMM: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$input = self::pw('input');
		$customers = $filter->query->paginate($input->pageNum, $input->get->offsetExists('print') ? 0 : self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('mar/armain/cmm/list/.js.twig');
		$html = self::displayList($data, $customers);
		// self::getCmm()->deleteResponse();
		return $html;
	}

	private static function customer($data) {
		self::pw('page')->headline = "CMM: Adding New Type";

		$cmm = self::getCmm();
		$customer = $cmm->getOrCreate($data->id);
		$cmm->updateRecordFromResponse($customer);

		if ($customer->isNew() === false) {
			self::pw('page')->headline = "CMM: Editing $data->id";
			$cmm->lockrecord($customer);
		}

		self::initHooks();
		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/cmm/edit/.js.twig', ['cmm' => $cmm]);

		$html = self::displayCustomer($data, $customer);
		// self::getCmm()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return Menu::cmmUrl();
	}

	public static function cmmUrl($customer = '') {
		if (empty($customer)) {
			return self::url();
		}
		return self::cmmFocusUrl($customer);
	}

	public static function cmmFocusUrl($focus) {
		$filter = new Filters\Mar\Customer();
		if ($filter->exists($focus) === false) {
			return self::url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'cmm', $pagenbr);
		return $url->getUrl();
	}

	public static function custEditUrl($customer) {
		$url = new Purl(Menu::cmmUrl());
		$url->query->set('id', $customer);
		return $url->getUrl();
	}

	public static function custDeleteUrl($customer) {
		$url = new Purl(self::custEditUrl($customer));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $customers) {
		$config = self::pw('config');
		$cmm = self::getCmm();

		$html  = '';
		$html .= $config->twig->render('mar/armain/cmm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		// $html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= $config->twig->render('mar/armain/cmm/list/display.twig', ['cmm' => $cmm, 'customers' => $customers]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $customers]);
		}
		return $html;
	}

	private static function displayCustomer($data, Customer $customer) {
		$config = self::pw('config');
		$cmm    = self::getCmm();

		$html  = '';
		$html .= $config->twig->render('mar/armain/cmm/bread-crumbs.twig');
		$html .= '<div class="mb-3">'.self::displayResponse($data).'</div>';
		// $html .= '<div class="mb-3">'.self::displayResponseQnotes($data).'</div>';
		$html .= '<div class="mb-3">'.self::displayLocked($data).'</div>';
		$html .= '<div class="mb-3">'.$config->twig->render('mar/armain/cmm/edit/display.twig', ['cmm' => $cmm, 'customer' => $customer]).'</div>';
		return $html;
	}

	private static function displayResponse($data) {
		$cmm = self::getCmm();
		$response = $cmm->getResponse();

		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('crud/response.twig', ['response' => $response]);
	}

	private static function displayResponseQnotes($data) {
		$cmm = self::getCmm();
		$html = '';

		foreach ($cmm->qnotes->getResponses() as $response) {
			$html .= '<div class="mb-3">'. self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]) .'</div>';
		}
		return $html;
	}


	private static function displayLocked($data) {
		$cmm = self::getCmm();

		if ($cmm->recordlocker->isLocked($data->id) && $cmm->recordlocker->userHasLocked($data->id) === false) {
			$msg = "Customer $data->id is being locked by " . $cmm->recordlocker->getLockingUser($data->id);
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Customer $data->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		return '';
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

		$m->addHook('Page(pw_template=armain)::custListUrl', function($event) {
			$event->return = self::cmmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::custAddUrl', function($event) {
			$event->return = self::custEditUrl('new');
		});

		$m->addHook('Page(pw_template=armain)::custEditUrl', function($event) {
			$event->return = self::custEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::custDeleteUrl', function($event) {
			$event->return = self::custDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCmm() {
		if (empty(self::$cmm)) {
			self::$cmm = new CmmManager();
		}
		return self::$cmm;
	}
}
