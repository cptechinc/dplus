<?php namespace Controllers\Min\Itm;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use MsaSysopCodeQuery, MsaSysopCode;
// Dplus Filters
use Dplus\Filters;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Itm
use Dplus\Min\Inmain\Itm\Options as ItmOptions;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Options extends Base {
	const PERMISSION_ITMP = 'options';
	const SHOWONPAGE = 20;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'system|text', 'sysop|text', 'code|string', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->itemID) === false) {
			return self::list($data);
		}
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields     = ['itemID|text', 'system|text', 'sysop|text', 'code|string', 'action|text'];
		$data       = self::sanitizeParameters($data, $fields);
		$input      = self::pw('input');
		$itmOptions = self::getItmOptions();

		if ($data->action) {
			$itmOptions->processInput($input);
		}

		if (self::pw('config')->ajax === false) {
			if (empty($data->redirect) === false) {
				self::pw('session')->redirect($data->redirect, $http301 = false);
			}

			$url = self::itmUrlOptions($data->itemID);
			switch ($data->action) {
				case 'delete':
				case 'update':
					$url = self::optionFocusUrl($data->itemID, $data->sysop);
					break;
			}
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	private static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$filter = self::getSysOptCodeFilter();
		$filter->sortby(self::pw('page'));
		$filter->query->orderBy(MsaSysopCode::aliasproperty('list_seq'), 'ASC');
		$filter->query->orderBy(MsaSysopCode::aliasproperty('description'), 'ASC');
		$options = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('itm', 'options');
		}

		$page = self::pw('page');
		self::initHooks();
		$page->headline = "ITM: $data->itemID Optional Codes";
		$page->js .= self::pw('config')->twig->render('items/itm/options/.js.twig', ['itmOpt' => self::getItmOptions()]);
		$page->js .= self::pw('config')->twig->render('items/itm/options/list.js.twig', ['itmOpt' => self::getItmOptions()]);
		$html = self::listDisplay($data, $options);
		self::getItmOptions()->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, PropelModelPager $options) {
		$config  = self::pw('config');
		$itm     = self::getItm();
		$itmOpt  = self::getItmOptions();

		$item = $itm->item($data->itemID);
		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$response = $itmOpt->getResponse();

		if (empty($response) === false && $response->hasSuccess() === false) {
			$html .= $config->twig->render('items/itm/response-alert-new.twig', ['response' => $itmOpt->getResponse()]);
		}
		$html .= $config->twig->render('items/itm/options/display.twig', ['itm' => $itm, 'itmOpt' => $itmOpt, 'item' => $item, 'options' => $options]);
		$html .= $config->twig->render('items/itm/options/modal-code.twig');
		$html .= $config->twig->render('items/itm/options/modal-notes.twig', ['itmOpt' => $itmOpt]);
		return $html;
	}

/* =============================================================
	URL functions
============================================================= */
	public static function optionDeleteUrl($itemID, $sysop) {
		$url = new Purl(self::itmUrlOptions($itemID));
		$url->query->set('action', 'delete');
		$url->query->set('sysop', $sysop);
		return $url->getUrl();
	}

	public static function optionFocusUrl($itemID, $sysop = '') {
		if (empty($sysop)) {
			return self::itmUrlOptions($itemID);
		}
		$url = new Purl(self::itmUrlOptions($itemID));
		$url->query->set('focus', $sysop);

		$filter = self::getSysOptCodeFilter();
		$filter->query->orderBy(MsaSysopCode::aliasproperty('list_seq'), 'ASC');
		$filter->query->orderBy(MsaSysopCode::aliasproperty('description'), 'ASC');

		$sortFilter = Filters\SortFilter::getFromSession('itm', 'options');
		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}
		$offset  = $filter->positionQuick(implode(FunctionLocker::glue(), ['IN', $sysop]));
		$pagenbr = self::getPagenbrFromOffset($offset, self::SHOWONPAGE);
		$url     = self::pw('modules')->get('Dpurl')->paginate($url, 'options', $pagenbr);

		if ($sortFilter) {
			if ($sortFilter->q) {
				$url->query->set('q', $sortFilter->q);
			}
			if ($sortFilter->orderby) {
				$url->query->set('orderby', $sortFilter->orderby);
			}
		}
		return $url->getUrl();
	}

	public static function missingRequiredCodesUrls($itemID) {
		$urls = [];
		$itmOpt  = self::getItmOptions();

		foreach ($itmOpt->getMissingRequiredCodes($itemID) as $sysop) {
			$urls[] = self::optionFocusUrl($itemID, $sysop);
		}
		return $urls;
	}

/* =============================================================
	Hook functions
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(pw_template=itm)::optionDeleteUrl', function($event) {
			$event->return = self::optionDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::missingRequiredCodesUrls', function($event) {
			$event->return = self::missingRequiredCodesUrls($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getItmOptions() {
		return ItmOptions::getInstance();
	}

	public static function getSysOptCodeFilter() {
		$filter = new Filters\Msa\MsaSysopCode();
		$filter->system('IN');
		return $filter;
	}
}
