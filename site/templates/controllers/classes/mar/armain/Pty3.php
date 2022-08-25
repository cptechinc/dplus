<?php namespace Controllers\Mar\Armain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Mar\Armain\Pty3 as RecordManager;
// Mvc Controllers
use Controllers\Mar\AbstractController as Base;

class Pty3 extends Base {
	const DPLUSPERMISSION = 'pty3';
	const TITLE = 'Customer 3rd Party Freight';
	const SUMMARY    = 'View / Edit Customer 3rd Party Freight Accounts';
	const SHOWONPAGE = 10;

	private static $recordsManager;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		self::pw('page')->headline = RecordManager::DESCRIPTION;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if (empty($data->custID) === false) {
			// return self::listCustomerAccounts($data);
		}
		return self::listCustomers($data);
	}

	public static function handleCRUD($data) {
		$fields = ['custID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url  = self::pty3Url();
		$recordsManager = self::getRecordManager();

		if ($data->action) {
			$recordsManager->processInput(self::pw('input'));
			$url = self::pty3Url($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function listCustomers($data) {
		$fields = ['q|text', 'col|text'];
		self::sanitizeParametersShort($data, $fields);

		$filter = new Filters\Mar\Customer();
		$filter->custid(self::getRecordManager()->custids());

		if (empty($data->q) === false) {
			$filter->search($data->q, self::pw('sanitizer')->array($data->col, ['delimiter' => ',']));
		}
		$input = self::pw('input');
		$filter->sort($input->get);
		$customers = $filter->query->paginate($input->pageNum, self::SHOWONPAGE);
		
		self::initHooks();
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/mar/armain/pty3/customer-list.js'));
		$html = self::displayCustomerList($data, $customers);
		self::getRecordManager()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function pty3Url($custID = '') {
		if (empty($custID)) {
			return Menu::pty3Url();
		}
		return self::pty3FocusCustidUrl($custID);
	}

	public static function pty3CustUrl($custID) {
		$url = new Purl(Menu::pty3Url());
		$url->query->set('custID', $custID);
		return $url->getUrl();
	}

	public static function pty3FocusCustidUrl($focus) {
		$filter = new Filters\Mar\Customer();
		$filter->custid(self::getRecordManager()->custids());

		if ($filter->exists($focus) === false) {
			return Menu::pty3Url();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::pty3Url());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'pty3', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(Menu::pty3Url());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayCustomerList($data, PropelModelPager $customers) {
		$config = self::pw('config');
		$recordsManager = self::getRecordManager();

		$html  = '';
		$html .= $config->twig->render('mar/armain/pty3/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('mar/armain/pty3/customers/list.twig', ['manager' => $recordsManager, 'customers' => $customers]);
		if (self::pw('input')->get->offsetExists('print') === false) {
			$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $customers]);
		}
		return $html;
	}

	public static function displayResponse($data) {
		$recordsManager = self::getRecordManager();
		$response = $recordsManager->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
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

		$m->addHook('Page(pw_template=armain)::pty3Url', function($event) {
			$event->return = self::pty3Url($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::pty3CustUrl', function($event) {
			$event->return = self::pty3CustUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=armain)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getRecordManager() {
		if (empty(self::$recordsManager)) {
			self::$recordsManager = new RecordManager();
		}
		return self::$recordsManager;
	}
}
