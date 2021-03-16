<?php namespace Controllers\Min\Itm\Xrefs;
// Dplus Model
use ItemXrefCxmQuery, ItemXrefCxm;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefCxm as CxmCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;
use Controllers\Mso\Cxm as BaseCxm;

class Cxm extends ItmFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$page->show_breadcrumbs = false;

		// if (empty($data->action) === false) {
		// 	return self::handleCRUD($data);
		// }

		if (empty($data->custitemID) == false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'custID|text', 'custitemID|text', 'action|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$cxm = self::pw('modules')->get('XrefCxm');
			$cxm->process_input($input);
		}
		$session  = self::pw('session');

		$response = $session->getFor('response', 'cxm');
		$url = $page->itm_xrefs_cxmURL($data->itemID);

		if ($cxm->xref_exists($custID, $custitemID)) {
			if ($response && $response->has_success()) {
				$url = $page->itm_xrefs_cxmURL($data->itemID, $response->key);
			}
			$url = $page->cxm_itemURL($data->custID, $data->custitemID);
		}
		$session->redirect($url, $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'custID|text', 'custitemID|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$pages   = self::pw('pages');
		$itm     = self::getItm();
		$modules = self::pw('modules');
		$qnotes = $modules->get('QnotesItemCxm');
		$cxm    = $modules->get('XrefCxm');
		$xref = $cxm->get_create_xref($data->custID, $data->custitemID);
		$item = $itm->get_item($data->itemID);

		if ($xref->isNew() === false) {

		}
		$page->headline = "ITM: $item->itemid CXM $xref->custid-$xref->custitemid";

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
			$page->headline = "ITM: $item->itemid CXM Create X-ref";
		}

		$html = '';
		$html .= self::cxmHeaders();
		$html .= BaseCxm::lockXref($page, $cxm, $xref);
		$html .= $config->twig->render('items/cxm/item/form/display.twig', ['item' => $xref, 'cxm' => $cxm, 'qnotes' => $qnotes, 'customer' => $cxm->get_customer($data->custID)]);

		if (!$xref->isNew()) {
			$html .= '<div class="mt-3"><h3>Notes</h3></div>';
			$html .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['item' => $xref, 'qnotes' => $qnotes]);
			$page->js .= $config->twig->render('items/cxm/item/notes/js.twig', ['qnotes' => $qnotes]);
			$page->js .= $config->twig->render('msa/noce/ajax/js.twig', ['qnotes' => $qnotes]);
		}

		$page->js .= $config->twig->render('items/cxm/item/form/js.twig', ['cxm' => $cxm]);
		return $html;
	}

	private static function cxmHeaders() {
		$html = '';
		$session = self::pw('session');
		$config  = self::pw('config');

		$html .= $config->twig->render('items/itm/bread-crumbs.twig');

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

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$input   = self::pw('input');
		$page    = self::pw('page');
		$config  = self::pw('config');
		$modules = self::pw('modules');
		$itm    = self::getItm();
		$item = $itm->get_item($data->itemID);
		$cxm = $modules->get('XrefCxm');
		$cxm->recordlocker->remove_lock();
		$filter = $modules->get('FilterXrefItemCxm');
		$filter->filter_input($input);
		$filter->apply_sortby($page);
		$xrefs = $filter->query->paginate($input->pageNum, 10);
		$page->title = "CXM";
		$page->headline = "ITM: $data->itemID CXM";

		$html = '';
		$html .= self::cxmHeaders();
		$html .= $config->twig->render('items/itm/xrefs/cxm/list/display.twig', ['item' => $item, 'response' => self::pw('session')->getFor('response', 'cxm'), 'items' => $xrefs]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $xrefs]);
		$page->js .= $config->twig->render('items/itm/xrefs/cxm/list/js.twig');
		return $html;
	}
}
