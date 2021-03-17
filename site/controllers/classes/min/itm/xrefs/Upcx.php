<?php namespace Controllers\Min\Itm\Xrefs;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;
use Controllers\Min\Upcx as BaseUpcx;

class Upcx extends ItmFunction {
	public static function index($data) {
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->upc) === false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$page    = self::pw('page');
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$upcx = self::pw('modules')->get('XrefUpc');
			$upcx->process_input($input);
		}
		$upc = $data->action == 'delete-upcx' ? '' : $data->upc;
		self::pw('session')->redirect(self::pw('page')->upcURL($upc), $http301 = false);
	}

	public static function xref($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config = self::pw('config');
		$page   = self::pw('page');
		$upcx = $wire->modules->get('XrefUpc');
		$xref = $upcx->get_create_xref($data->upc);

		if ($xref->isNew()) {
			$page->headline = "UPCX: Create X-ref";
			$xref->setItemid($data->itemID);
		}
		if ($xref->isNew() == false) {
			$page->headline = "UPCX: $xref->upc";
		}

		$html = '';
		$html .= BaseUpcx::lockXref($page, $upcx, $xref);
		$html .= $config->twig->render('items/itm/xrefs/upcx/form/page.twig', ['upcx' => $upcx, 'upc' => $xref]);
		$page->js   .= $config->twig->render('items/upcx/form/js.twig', ['upc' => $xref]);
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$input  = self::pw('input');
		$page   = self::pw('page');
		$config = self::pw('config');
		$modules = self::pw('modules');
		$itm    = self::getItm();
		$item = $itm->get_item($data->itemID);
		$upcx = $modules->get('XrefUpc');
		$upcx->recordlocker->remove_lock();
		$filter = $modules->get('FilterXrefItemUpc');
		$filter->filter_input($input);
		$filter->apply_sortby($page);
		$upcs = $filter->query->paginate($input->pageNum, 10);
		$page->title = "UPCs";
		$page->headline = "ITM: $data->itemID UPCX";
		$html = '';

		$html .= $config->twig->render('items/itm/xrefs/upcx/list/page.twig', ['upcs' => $upcs, 'itemID' => $data->itemID, 'upcx' => $upcx]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $upcs]);
		$page->js   .= $config->twig->render('items/upcx/list/.js.twig');
		return $html;
	}
}
