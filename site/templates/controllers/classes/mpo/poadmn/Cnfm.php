<?php namespace Controllers\Mpo\Poadmn;
use stdClass;
// Purl Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes\Mpo\Cnfm as Manager;
// Mvc Controllers
use Mvc\Controllers\Controller;


class Cnfm extends Base {
	const DPLUSPERMISSION = 'cnfm';
	const SHOWONPAGE = 10;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['code|text', 'action|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		if ($data->code) {
			return self::code($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);

		$cnfm = self::getCnfm();
		$cnfm->recordlocker->deleteLock();
		self::pw('page')->headline = Menu::SUBFUNCTIONS['cnfm']['title'];

		$filter = new Filters\Mpo\PoConfirmCode();

		if (empty($data->q) === false) {
			$filter->search($data->q);
			self::pw('page')->headline = "DCM: Searching for '$data->q'";
		}

		$filter->sortby(self::pw('page'));
		$codes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);

		self::pw('page')->js .= self::pw('config')->twig->render('code-tables/mpo/cnfm/js.twig', ['cnfm' => $cnfm]);
		self::initHooks();
		$html = self::displayList($data, $codes);
		$cnfm->deleteResponse();
		return $html;
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['code|text', 'action|text']);
		$cnfm = self::getCnfm();

		if ($data->action) {
			$cnfm->processInput(self::pw('input'));
		}

		$url = self::codeFocusUrl($data->code);
		switch ($data->action) {
			case 'delete-code':
				$url = self::cnfmUrl();
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

/* =============================================================
	URLs
============================================================= */
	public static function codeUrl($code) {
		$url = new Purl(self::cnfmUrl());
		$url->query->set('code', $code);
		return $url->getUrl();
	}

	public static function codeFocusUrl($focus) {
		$filter = new Filters\Mpo\PoConfirmCode();
		if ($filter->exists($focus) === false) {
			return self::cnfmUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(self::cnfmUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'cnfm', $pagenbr);
		return $url->getUrl();
	}

	public static function codeDeleteUrl($code) {
		$url = new Purl(self::codeUrl($code));
		$url->query->set('action', 'delete-code');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $codes) {
		$cnfm   = self::getCnfm();
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadcrumbs($data);
		$html .= '<div class="mb-3">' . self::displayResponse($data) . '</div>';
		$html .= $config->twig->render('code-tables/list.twig', ['manager' => $cnfm , 'codes' => $codes]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $codes]);
		$html .= $config->twig->render('code-tables/mpo/cnfm/edit-modal.twig', ['cnfm' => $cnfm]);
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getCnfm()->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayBreadcrumbs($data) {
		return self::pw('config')->twig->render('code-tables/mpo/cnfm/bread-crumbs.twig');
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function getCnfm() {
		return Manager::getInstance();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=poadmn)::codeDeleteUrl', function($event) {
			$event->return = self::codeDeleteUrl($event->arguments(0));
		});
	}
}
