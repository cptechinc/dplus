<?php namespace Controllers\Map;

use Mvc\Controllers\AbstractController;

use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeModel;
use ItemXrefManufacturer;

class Mxrfe extends AbstractController {
	public static function index($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'q|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->mnfrID) === false) {
			if (empty($data->mnfritemID) === false) {
				return self::xref($data);
			}
			return self::mnfrXrefs($data);
		}
		return self::listMnfr($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$mxrfe = self::pw('modules')->get('XrefMxrfe');
			$mxrfe->process_input($input);
		}
		self::pw('session')->redirect(self::pw('page')->redirectURL($input), $http301 = false);
	}

	public static function xref($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$wire = self::pw();
		$config = $wire->wire('config');
		$page = $wire->wire('page');
		$mxrfe = $wire->modules->get('XrefMxrfe');
		$mxrfe->init_field_attributes_config();
		$vendor = $mxrfe->vendor($data->mnfrID);
		$xref = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$qnotes = $wire->wire('modules')->get('QnotesItemMxrfe');

		$page->body .= self::lockXref($page, $mxrfe, $xref);

		$page->body .= $config->twig->render('items/mxrfe/item/form/page.twig', ['mxrfe' => $mxrfe, 'vendor' => $vendor, 'xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/item/form/js.twig', ['mxrfe' => $mxrfe]);

		if (!$xref->isNew()) {
			$page->body .= $config->twig->render('items/mxrfe/item/notes/notes.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('items/mxrfe/item/notes/js.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		}
		return $page->body;
	}

	private static function lockXref(Page $page, MxrfeModel $mxrfe, ItemXrefManufacturer $xref) {
		if (!$xref->isNew()) {
			$page->headline = "MXRFE: " . $mxrfe->get_recordlocker_key($xref);
			if (!$mxrfe->lockrecord($xref)) {
				$msg = "MXRFE ". $mxrfe->get_recordlocker_key($xref) ." is being locked by " . $mxrfe->recordlocker->get_locked_user($mxrfe->get_recordlocker_key($xref));
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "MXRFE ".$mxrfe->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}
		return $page->body;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		if ($data->mnfrID) {
			return self::mnfrXrefs($data);
		}
		return self::listMnfr($data);
	}

	public static function listMnfr($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$wire = self::pw();
		$config = $wire->wire('config');
		$page = $wire->wire('page');
		$mxrfe = $wire->modules->get('XrefMxrfe');
		$filter = $wire->wire('modules')->get('FilterVendors');
		$filter->init_query($wire->wire('user'));
		$filter->vendorid($mxrfe->vendorids());
		$filter->apply_sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, $wire->wire('session')->display);
		$page->body .= $config->twig->render('items/mxrfe/search/vendor/page.twig', ['vendors' => $vendors]);
		$page->body .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $vendors]);
		return $page->body;
	}

	public static function mnfrXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		$wire = self::pw();
		$config = $wire->wire('config');
		$page   = $wire->wire('page');
		$mxrfe  = $wire->modules->get('XrefMxrfe');
		$vendor = $mxrfe->vendor($data->mnfrID);
		$filter = $wire->modules->get('FilterXrefItemMxrfe');
		$filter->vendorid($data->mnfrID);
		$filter->apply_sortby($page);
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, $wire->wire('session')->display);
		$page->body .= $config->twig->render('items/mxrfe/list/vendor/page.twig', ['mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor]);
		return $page->body;
	}
}
