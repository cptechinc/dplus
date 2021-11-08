<?php namespace Controllers\Min\Itm;
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

class Options extends Base {
	const PERMISSION_ITMP = 'options';
	const SHOWONPAGE = 20;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'system|text', 'sysop|text', 'code|text', 'action|text'];
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

		$fields     = ['itemID|text', 'system|text', 'sysop|text', 'code|text', 'action|text'];
		$data       = self::sanitizeParameters($data, $fields);
		$input      = self::pw('input');
		$itmOptions = self::getItmOptions();

		if ($data->action) {
			$itmOptions->processInput($input);
		}

		if (self::pw('config')->ajax === false) {
			$url = empty($data->redirect) === false ? $data->redirect : self::itmUrlOptions($data->itemID);
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

		$filter->query->find();
		$options = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);

		$page = self::pw('page');
		$page->headline = "ITM: $data->itemID Optional Codes";
		$page->js .= self::pw('config')->twig->render('items/itm/options/.js.twig', []);
		return self::listDisplay($data, $options);
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

		if ($itmOpt->getResponse()) {
			$html .= $config->twig->render('items/itm/response-alert-new.twig', ['response' => $itmOpt->getResponse()]);
		}
		$html .= self::lockItem($data->itemID);
		$html .= $config->twig->render('items/itm/itm-links.twig');
		$html .= $config->twig->render('items/itm/options/display.twig', ['itm' => $itm, 'itmOpt' => $itmOpt, 'item' => $item, 'options' => $options]);
		$html .= $config->twig->render('items/itm/options/modal-code.twig');
		return $html;
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