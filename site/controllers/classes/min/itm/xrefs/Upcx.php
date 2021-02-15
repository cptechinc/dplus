<?php namespace Controllers\Min\Itm\Xrefs;

use Controllers\Min\Itm\ItmFunction;
use Controllers\Min\Upcx as BaseUpcx;

use ProcessWire\Page, ProcessWire\XrefUpc as UpcModel;
use ItemXrefUpc;

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
			return self::upc($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$mxrfe = self::pw('modules')->get('XrefUpc');
			$mxrfe->process_input($input);
		}
		$upc = $data->action == 'delete-upcx' ? '' : $data->upc;
		self::pw('session')->redirect(self::pw('page')->upcURL($upc), $http301 = false);
	}

	public static function upc($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$wire = self::pw();
		$config = self::pw('config');
		$page = self::pw('page');
		$upcx = $wire->modules->get('XrefUpc');
		$xref = $upcx->get_create_xref($data->upc);

		if ($xref->isNew()) {
			$xref->setItemid($data->itemID);
		}

		BaseUpcx::lockXref($page, $upcx, $xref);
		$page->body .= $config->twig->render('items/itm/xrefs/upcx/form/page.twig', ['upcx' => $upcx, 'upc' => $xref]);
		$page->js   .= $config->twig->render('items/upcx/form/js.twig', ['upc' => $xref]);
		return $page->body;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}
		$fields = ['itemID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$wire = self::pw();
		$input = $wire->wire('input');
		$page = $wire->wire('page');
		$config = $wire->wire('config');
		$itm  = self::getItm();
		$item = $itm->get_item($data->itemID);
		$upcx = $wire->wire('modules')->get('XrefUpc');
		$upcx->recordlocker->remove_lock();
		$filter = $wire->wire('modules')->get('FilterXrefItemUpc');
		$filter->filter_input($input);
		$filter->apply_sortby($page);
		$upcs = $filter->query->paginate($input->pageNum, 10);
		$page->title = "UPCs";
		$page->headline = "ITM: UPCs for $data->itemID";

		$page->body .= $config->twig->render('items/itm/xrefs/upcx/list/page.twig', ['upcs' => $upcs, 'itemID' => $data->itemID, 'upcx' => $upcx]);
		$page->body .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $upcs]);
		return $page->body;
	}

}
