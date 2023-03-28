<?php namespace Controllers\Mvi\Vi;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemXrefVendor;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;

class Costing extends Subfunction {
	const PERMISSION_VIO = 'costing';
	const JSONCODE       = 'vi-costing';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|string', 'itemID|string', 'q|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		if ($data->refresh && $data->itemID) {
			self::requestJson($data);
			self::pw('session')->redirect(self::costingUrl($data->vendorID, $data->itemID), $http301 = false);
		}
		if (empty($data->itemID)) {
			return self::searchItems($data);
		}
		return self::costing($data);
	}

	private static function searchItems($data) {
		self::pw('page')->headline = "VI: $data->vendorID Costing";

		if ($data->q) {
			self::pw('page')->headline = "VI: Costing searching for '$data->q'";
		}

		$filter = new Filters\Min\ItemMaster();
		$filter->query->filterByItemid(self::searchVxmItemids($data));
		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);

		self::initHooks();
		return self::displayItemSearch($data, $items);
	}

	private static function searchVxmItemids($data) {
		$filter = new Filters\Map\Vxm();
		$filter->vendorid($data->vendorID);
		if ($data->q) {
			$filter->search($data->q);
		}
		$filter->query->select(ItemXrefVendor::aliasproperty('itemid'));
		$filter->query->distinct();
		return $filter->query->find()->toArray();
	}

	private static function costing($data) {
		self::getData($data);
		$config = self::pw('config');
		$page   = self::pw('page');
		$jsonm   = self::getJsonModule();

		$page->headline = "VI: $data->vendorID Costing for $data->itemID";
		$page->refreshurl   = self::costingUrl($data->vendorID, $data->itemID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		self::initHooks();

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayCosting($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['vendorID|string', 'itemID|string']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($session->getFor('vi', 'costing') > 3 && $json['vendid'] == $data->vendorID && $json['itemid'] == $data->itemID) {
			return false;
		}
		$session->setFor('vi', 'costing', ($session->getFor('vi', 'costing') + 1));
		$session->redirect(self::costingUrl($data->vendorID, $data->itemID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	private static function displayItemSearch($data, PropelModelPager $items) {
		$config = self::pw('config');
		return $config->twig->render('vendors/vi/costing/search.twig', ['items' => $items]);
	}

	private static function displayCosting($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Sales History File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$vendor = self::getVendor($data->vendorID);
		return $config->twig->render('vendors/vi/costing/display.twig', ['vendor' => $vendor, 'json' => $json]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function costingUrl($vendorID, $itemID, $refreshdata = false) {
		$url = new Purl(self::viCostingUrl($vendorID));

		if ($itemID) {
			$url->query->set('itemID', $itemID);
			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['vendorID|string', 'itemID|string', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['VICOST', "VENDID=$vars->vendorID", "ITEMID=$vars->itemID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vi)::costingItemUrl', function($event) {
			$event->return = self::costingUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
