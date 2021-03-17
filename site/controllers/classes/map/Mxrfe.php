<?php namespace Controllers\Map;
// Dplus Model
use ItemXrefManufacturer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefMxrfe as MxrfeCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mxrfe extends AbstractController {
	private static $mxrfe;

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
		return self::listMnfrs($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$mxrfe = self::mxrfeMaster();
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
		$config = self::pw('config');
		$page   = self::pw('page');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->init_field_attributes_config();
		$vendor = $mxrfe->vendor($data->mnfrID);
		$xref   = $mxrfe->get_create_xref($data->mnfrID, $data->mnfritemID, $data->itemID);
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');
		$html = '';
		if ($xref->isNew()) {
			$page->headline = "MXRFE: New X-ref";
		}
		if ($xref->isNew() === false) {
			$page->headline = "MXRFE: " . $mxrfe->get_recordlocker_key($xref);
		}
		$html .= self::mxrfeHeaders();
		$html .= self::lockXref($xref);
		$html .= $config->twig->render('items/mxrfe/item/form/display.twig', ['mxrfe' => $mxrfe, 'vendor' => $vendor, 'xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/item/form/js.twig', ['mxrfe' => $mxrfe]);

		if (!$xref->isNew()) {
			$html .= self::qnotesDisplay($xref);
		}
		return $html;
	}

	public static function lockXref(ItemXrefManufacturer $xref) {
		$html = '';
		$mxrfe = self::mxrfeMaster();

		if (!$xref->isNew()) {
			if (!$mxrfe->lockrecord($xref)) {
				$msg = "MXRFE ". $mxrfe->get_recordlocker_key($xref) ." is being locked by " . $mxrfe->recordlocker->get_locked_user($mxrfe->get_recordlocker_key($xref));
				$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "MXRFE ".$mxrfe->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}
		return $html;
	}

	public static function qnotesDisplay(ItemXrefManufacturer $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemMxrfe');
		$html = '<hr> <div class="mt-3"></div>';
		$html .= $config->twig->render('items/mxrfe/item/notes/notes.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/item/notes/js.twig', ['xref' => $xref, 'qnotes' => $qnotes]);
		return $html;
	}

	private static function mxrfeHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/mxrfe/bread-crumbs.twig');

		if ($session->getFor('response','mxrfe')) {
			$html .= $config->twig->render('items/cxm/response.twig', ['response' => $session->getFor('response','mxrfe')]);
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		if ($data->mnfrID) {
			return self::mnfrXrefs($data);
		}
		return self::listMnfrs($data);
	}

	public static function listMnfrs($data) {
		$data   = self::sanitizeParametersShort($data, ['q|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$page->show_breadcrumbs = false;
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->remove_lock();
		$filter = self::pw('modules')->get('FilterVendors');
		$filter->init_query(self::pw('user'));
		$filter->vendorid($mxrfe->vendorids());
		if ($data->q) {
			$page->headline = "Searching Mnfrs for '$data->q'";
			$filter->search($data->q);
		}
		$filter->apply_sortby($page);
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);

		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= $config->twig->render('items/mxrfe/search/vendor/page.twig', ['vendors' => $vendors]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		return $html;
	}

	public static function mnfrXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['mnfrID|text']);
		$config = self::pw('config');
		$page   = self::pw('page');
		$mxrfe  = self::mxrfeMaster();
		$mxrfe->recordlocker->remove_lock();
		$vendor = $mxrfe->vendor($data->mnfrID);
		$filter = self::pw('modules')->get('FilterXrefItemMxrfe');
		$filter->vendorid($data->mnfrID);
		$filter->apply_sortby($page);
		$page->headline = "MXRFE: Mnfr $data->mnfrID";
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html = '';
		$html .= self::mxrfeHeaders();
		$html .= $config->twig->render('items/mxrfe/list/vendor/display.twig', ['mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor]);
		return $html;
	}

	public static function mxrfeMaster() {
		if (empty(self::$mxrfe)) {
			self::$mxrfe = self::pw('modules')->get('XrefMxrfe');
		}
		return self::$mxrfe;
	}
}
