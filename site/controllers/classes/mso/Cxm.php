<?php namespace Controllers\Mso;
// Dplus Model
use ItemXrefCustomer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefCxm as CxmCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Cxm extends AbstractController {
	public static function index($data) {
		$fields = ['custID|text', 'custitemID|text', 'q|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->custID) === false) {
			if (empty($data->custitemID) === false) {
				return self::xref($data);
			}
			return self::custXrefs($data);
		}
		return self::listCustomers($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'custID|text', 'custitemID|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input   = self::pw('input');
		$modules = self::pw('modules');
		$modules->get('DpagesMso')->init_cxm_hooks();

		if ($data->action) {
			$cxm = $modules->get('XrefCxm');
			$cxm->process_input($input);
		}
		$session = self::pw('session');
		$page    = self::pw('page');

		$response = $session->getFor('response', 'cxm');
		$url = $page->cxm_customerURL($data->custID);

		if ($cxm->xref_exists($data->custID, $data->custitemID)) {
			if ($response  && $response ->has_success()) {
				$url = $page->cxm_customerURL($data->custID, $response->key);
			}
			$url = $page->cxm_itemURL($data->custID, $data->custitemID);
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		$fields = ['custID|text', 'custitemID|text', 'itemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$modules = self::pw('modules');
		$modules->get('DpagesMso')->init_cxm_hooks();
		$cxm = $modules->get('XrefCxm');
		$customer = $cxm->customer($data->custID);
		$xref = $cxm->get_create_xref($data->custID, $data->custitemID, $data->itemID);
		$qnotes = $modules->get('QnotesItemCxm');
		$html = '';
		if ($xref->isNew()) {
			$page->headline = "CXM: New X-ref";
		}

		if ($xref->isNew() === false) {
			$page->headline = "CXM: $xref->custid $xref->custitemid";
		}
		$html .= self::cxmHeaders();
		$html .= self::lockXref($page, $cxm, $xref);
		$pages = self::pw('pages');

		$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
		$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
		$html .= $config->twig->render('items/cxm/item/form/display.twig', ['item' => $xref, 'cxm' => $cxm, 'qnotes' => $qnotes]);
		$page->headline = "CXM: " . $cxm->get_recordlocker_key($xref);

		if ($xref->isNew()) {
			$page->headline = "CXM: Create X-ref";
		}

		if (!$xref->isNew()) {
			$html .= self::qnotesDisplay($xref);
		}

		$page->js .= $config->twig->render('items/cxm/item/form/js.twig', ['cxm' => $cxm]);
		return $html;
	}

	public static function qnotesDisplay(ItemXrefCustomer $xref) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$qnotes = self::pw('modules')->get('QnotesItemCxm');
		$html = '';
		$html .= '<div class="mt-3"><h3>Notes</h3></div>';
		$html .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['item' => $xref, 'qnotes' => $qnotes]);
		$page->js .= $config->twig->render('items/cxm/item/notes/js.twig', ['qnotes' => $qnotes]);
		$page->js .= $config->twig->render('msa/noce/ajax/js.twig', ['qnotes' => $qnotes]);
		return $html;
	}


	private static function cxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/cxm/bread-crumbs.twig');
		if ($session->getFor('response','cxm')) {
			$html .= $config->twig->render('items/cxm/response.twig', ['response' => $session->getFor('response','cxm')]);
		}
		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		if ($session->response_pdm) {
			$html .= $config->twig->render('mso/pdm/response-alert.twig', ['response' => $session->response_pdm]);
		}
		return $html;
	}

	public static function lockXref(Page $page, CxmCRUD $cxm, ItemXrefCustomer $xref) {
		$html = '';
		if (!$xref->isNew()) {
			if (!$cxm->lockrecord($xref)) {
				$msg = "CXM ". $cxm->get_recordlocker_key($xref) ." is being locked by " . $cxm->recordlocker->get_locked_user($cxm->get_recordlocker_key($xref));
				$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM ".$cxm->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			}
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['custID|text']);
		if ($data->custID) {
			return self::custXrefs($data);
		}
		return self::listCustomers($data);
	}

	public static function listCustomers($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$config  = self::pw('config');
		$page    = self::pw('page');
		$modules = self::pw('modules');
		$cxm = $modules->get('XrefCxm');
		$cxm->recordlocker->remove_lock();
		$modules->get('DpagesMso')->init_cxm_hooks();
		$filter = $modules->get('FilterCustomers');
		$filter->init_query(self::pw('user'));
		$filter->custid($cxm->custids());
		if ($data->q) {
			$page->headline = "Searching Customers for '$data->q'";
			$filter->search($data->q);
		}
		$filter->apply_sortby($page);
		$customers = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html = '';
		$html .= self::cxmHeaders();
		$html .= $config->twig->render('items/cxm/search/customer/results.twig', ['customers' => $customers]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $customers]);
		$html .= $config->twig->render('items/cxm/new-cxm-modal.twig');
		$page->js   .= $config->twig->render('items/cxm/search/customer/js.twig');
		return $html;
	}

	public static function custXrefs($data) {
		$data = self::sanitizeParametersShort($data, ['custID|text', 'q|text']);
		$config  = self::pw('config');
		$page    = self::pw('page');
		$modules = self::pw('modules');
		$modules->get('DpagesMso')->init_cxm_hooks();
		$cxm  = $modules->get('XrefCxm');
		$cxm->recordlocker->remove_lock();
		$customer = $cxm->customer($data->custID);
		$filter = $modules->get('FilterXrefItemCxm');
		$filter->custid($data->custID);
		$filter->apply_sortby($page);
		if ($data->q) {
			$page->headline = "CXM: $customer->name searching '$data->q'";
			$filter_cxm->search($data->q);
		}
		$page->headline = "CXM: $customer->name";
		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$html = '';
		$page->searchcustomersURL = self::pw('pages')->get('pw_template=mci-lookup')->url;
		$html .= self::cxmHeaders();
		$html .= $config->twig->render('items/cxm/cxm-links.twig', []);
		$html .= $config->twig->render('items/cxm/list/display.twig', ['cxm' => $cxm, 'customer' => $cxm->get_customer($data->custID), 'response' => self::pw('session')->getFor('response', 'cxm'), 'items' => $xrefs, 'custID' => $data->custID]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		$page->js   .= $config->twig->render('items/cxm/list/js.twig', []);
		return $html;
	}
}
