<?php namespace Controllers\Map;
// Dplus Model
use ItemXrefVendor;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefVxm as VxmCRUD;
// DplusFilters
use Dplus\Filters\Map\Vendor as VendorFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Vxm extends AbstractController {
	private static $vxm;

	public static function index($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'q|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->vendorID) === false) {
			if (empty($data->vendoritemID) === false) {
				return self::xref($data);
			}
			return self::vendorXrefs($data);
		}
		return self::listVendors($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'vendorID|text', 'vendoritemID|text', 'itemID|text'];
		$data   = self::sanitizeParameters($data, $fields);
		$input  = self::pw('input');
		$vxm    = self::vxmMaster();

		if ($data->action) {
			$vxm->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');
		$url = $page->vxm_vendorURL($data->vendorID);

		if ($vxm->xref_exists($data->vendorID, $data->vendoritemID, $data->itemID)) {
			$response = $session->getFor('response', 'vxm');

			if ($response && $response->has_success()) {
				$url = $page->vxm_vendorURL($data->vendorID, $response->key);
			}
			$url = $page->vxm_itemURL($data->vendorID, $data->vendoritemID, $data->itemID);
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config = self::pw('config');
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->init_field_attributes_config();
		$vendor = $vxm->get_vendor($data->vendorID);
		$xref = $vxm->get_create_xref($data->vendorID, $data->vendoritemID, $data->itemID);
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$html = '';

		if ($xref->isNew()) {
			$page->headline = "VXM: New X-ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "VXM: " . $vxm->get_recordlocker_key($xref);
		}

		$html .= self::lockXref($xref);

		$html .= $config->twig->render('items/vxm/item/form/display.twig', ['mxrfe' => $vxm, 'vendor' => $vendor, 'item' => $xref, 'vxm' => $vxm, 'qnotes' => $qnotes]);
		$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'vxm' => $vxm, 'item' => $xref]);

		if (!$xref->isNew()) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefVendor $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemVxm');
		$html = "<hr>";
		if (self::pw('session')->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => self::pw('session')->response_qnote]);
		}
		$page->searchURL = self::pw('pages')->get('pw_template=msa-noce-ajax')->url;
		$html .= $config->twig->render('items/vxm/notes/notes.twig', ['qnotes' => $qnotes, 'item' => $xref]);
		$page->js .= $config->twig->render('items/vxm/notes/js.twig');
		self::pw('session')->remove('response_qnote');
		return $html;
	}

	public static function lockXref(ItemXrefVendor $xref) {
		$html = '';
		$vxm = self::vxmMaster();

		if (!$xref->isNew()) {
			if (!$vxm->lockrecord($xref)) {
				$msg = "VXM ". $vxm->get_recordlocker_key($xref) ." is being locked by " . $vxm->recordlocker->get_locked_user($vxm->get_recordlocker_key($xref));
				$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		if ($data->vendorID) {
			return self::vendorXrefs($data);
		}
		return self::listVendors($data);
	}

	public static function listVendors($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->remove_lock();
		$filter = new VendorFilter();
		$filter->init();
		$filter->vendorid($vxm->vendorids());
		if ($data->q) {
			$page->headline = "Searching Vendors for '$data->q'";
			$filter->search($data->q);
		}
		$filter->sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$html = '';
		$html .= $config->twig->render('items/vxm/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		$html .= $config->twig->render('items/vxm/new-xref-modal.twig');

		$page->js .= $config->twig->render('items/vxm/search/vendor/js.twig');
		return $html;
	}

	public static function vendorXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$vxm    = self::vxmMaster();
		$vxm->recordlocker->remove_lock();
		$vendor = $vxm->get_vendor($data->vendorID);
		$filter = self::pw('modules')->get('FilterXrefItemVxm');
		$filter->vendorid($data->vendorID);
		$filter->apply_sortby($page);
		$page->headline = "VXM: Vendor $data->vendorID";
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html = $config->twig->render('items/vxm/list/item/vendor/display.twig', ['vxm' => $vxm, 'items' => $xrefs, 'vendor' => $vendor]);
		$page->js .= $config->twig->render('items/vxm/list/item/js.twig');
		return $html;
	}

	public static function vxmMaster() {
		if (empty(self::$vxm)) {
			self::$vxm = self::pw('modules')->get('XrefVxm');
		}
		return self::$vxm;
	}
}
