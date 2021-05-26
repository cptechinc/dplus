<?php namespace Controllers\Min\Itm\Xrefs;
// Dplus Model
use ItemXrefVendorQuery, ItemXrefVendor;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefVxm as VxmCRUD;
// DplusFilters
use Dplus\Filters\Map\Vxm as VxmFilter;
// Mvc Controllers
use Controllers\Min\Itm\Xrefs\XrefFunction;;
use Controllers\Map\Vxm as VxmController;

class Vxm extends XrefFunction {
	const PERMISSION_ITMP = 'xrefs';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}

		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->vendoritemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		$fields = ['itemID|text', 'vendorID|text', 'vendoritemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');
		$vxm   = VxmController::vxmMaster();

		if ($data->action) {
			$vxm->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');
		$response = $session->getFor('response', 'vxm');
		$url = $page->itm_xrefs_vxmURL($data->itemID);

		if ($vxm->xref_exists($data->vendorID, $data->vendoritemID, $data->itemID)) {
			$url = $page->vxm_itemURL($data->vendorID, $data->vendoritemID);

			if ($response && $response->has_success()) {
				$xref = $vxm->xref($data->vendorID, $data->vendoritemID, $data->itemID);
				$url = $page->vxm_item_exitURL($xref, $response->key);
			}
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		$fields = ['itemID|text', 'vendorID|text', 'vendoritemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$pages   = self::pw('pages');
		$itm     = self::getItm();
		$modules = self::pw('modules');
		$qnotes  = $modules->get('QnotesItemVxm');
		$vxm     = VxmController::vxmMaster();
		$xref = $vxm->get_create_xref($data->vendorID, $data->vendoritemID, $data->itemID);
		$item = $itm->get_item($data->itemID);

		$page->headline = "ITM: $item->itemid VXM $xref->vendorid-$xref->vendoritemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $item->itemid VXM Create X-ref";
		}

		$html = '';
		$html .= self::vxmHeaders();
		$html .= VxmController::lockXref($xref);
		$html .= $config->twig->render('items/itm/xrefs/vxm/form/display.twig', ['xref' => $xref, 'item' => $item, 'vxm' => $vxm, 'qnotes' => $qnotes, 'customer' => $vxm->get_vendor($data->vendorID)]);

		if (!$xref->isNew()) {
			$html .= VxmController::qnotesDisplay($xref);
		}

		$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['vxm' => $vxm]);
		return $html;
	}

	private static function vxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= self::breadCrumbs();

		if ($session->getFor('response','vxm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->getFor('response','vxm')]);
		}
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::pw('page')->body;
		}
		$fields  = ['itemID|text', 'q|text'];
		$data    = self::sanitizeParametersShort($data, $fields);
		$input   = self::pw('input');
		$page    = self::pw('page');
		$config  = self::pw('config');
		$modules = self::pw('modules');
		$itm     = self::getItm();
		$item = $itm->get_item($data->itemID);
		$vxm = VxmController::vxmMaster();
		$vxm->recordlocker->remove_lock();
		$filter = new VxmFilter();
		$filter->itemid($data->itemID);
		$filter->sortby($page);
		$xrefs = $filter->query->paginate($input->pageNum, 10);
		$page->title = "VXM";
		$page->headline = "ITM: $data->itemID VXM";

		$html = '';
		$html .= self::vxmHeaders();
		$html .= $config->twig->render('items/itm/xrefs/vxm/list/display.twig', ['item' => $item, 'items' => $xrefs, 'vxm' => $vxm]);
		$page->js .= $config->twig->render('items/vxm/list/item/js.twig');
		return $html;
	}
}
