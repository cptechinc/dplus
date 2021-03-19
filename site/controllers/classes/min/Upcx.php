<?php namespace Controllers\Min;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\XrefUpc as UpcCRUD;
// Dplus Filters
use Dplus\Filters\Min\Upcx as UpcxFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Upcx extends AbstractController {
	private static $upcx;

	public static function index($data) {
		$fields = ['upc|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
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
		$fields = ['action|text', 'upc|text'];
		$data  = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$upcx = self::getUpcx();
			$upcx->process_input($input);
		}
		self::pw('session')->redirect(self::pw('page')->upcURL($data->upc), $http301 = false);
	}

	public static function upc($data) {
		$fields = ['upc|text','action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$config = self::pw('config');
		$page   = self::pw('page');
		$upcx   = self::getUpcx();
		$xref = $upcx->get_create_xref($data->upc);

		if ($xref->isNew()) {
			$page->headline = "UPCX: Create X-ref";
		}

		if ($xref->isNew() == false) {
			$page->headline = "UPCX: $xref->upc";
		}

		$html = '';
		$html .= self::lockXref($page, $upcx, $xref);
		$html .= $config->twig->render('items/upcx/form/page.twig', ['upcx' => $upcx, 'upc' => $xref]);
		$page->js   .= $config->twig->render('items/upcx/form/js.twig', ['upc' => $xref]);
		return $$html;
	}

	public static function lockXref(Page $page, UpcCRUD $upcx, ItemXrefUpc $xref) {
		$config = $page->wire('config');
		$html = '';

		if ($upcx->recordlocker->function_locked($xref->upc) && !$upcx->recordlocker->function_locked_by_user($xref->upc)) {
			$msg = "UPC $code is being locked by " . $upcx->recordlocker->get_locked_user($xref->upc);
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $xref->upc is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		} elseif (!$upcx->recordlocker->function_locked($xref->upc)) {
			$upcx->recordlocker->create_lock($xref->upc);
		}

		if ($xref->isNew()) {
			if ($xref->upc != '') {
				$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "UPC not found, you may create it below"]);
			}
		}
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$upcx = self::getUpcx();
		$upcx->recordlocker->remove_lock();
		$filter = new UpcxFilter();

		if ($data->q) {
			$page->headline = "UPCX: Results for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$upcs = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('items/upcx/list/page.twig', ['upcx' => $upcx, 'upcs' => $upcs]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $upcs]);
		$page->js   .= $config->twig->render('items/upcx/list/.js.twig');
		return $html;
	}

	public static function getUpcx() {
		if (empty(self::$upcx)) {
			self::$upcx = self::pw('modules')->get('XrefUpc');
		}
		return self::$upcx;
	}
}
