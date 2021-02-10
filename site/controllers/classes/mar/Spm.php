<?php namespace Controllers\Mar;

use Mvc\Controllers\AbstractController;

use ProcessWire\Page, ProcessWire\Spm as SpmModel;
use Dplus\Filters\Mar\SalesPerson as FilterSalesPerson;

class Spm extends AbstractController {
	public static function index($data) {
		$fields = ['id|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$page->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->id) === false) {
			return self::salesperson($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'upc|text'];
		$data = self::sanitizeParameters($data, $fields);
		$input = self::pw('input');

		if ($data->action) {
			$mxrfe = self::pw('modules')->get('XrefUpc');
			$mxrfe->process_input($input);
		}
		self::pw('session')->redirect(self::pw('page')->upcURL($data->upc), $http301 = false);
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$wire = self::pw();
		$page = $wire->wire('page');
		$spm = $wire->modules->get('Spm');
		$spm->recordlocker->remove_lock();

		$filter = new FilterSalesPerson();
		$filter->init();

		if ($data->q) {
			$page->headline = "SPM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$people = $filter->query->paginate($wire->wire('input')->pageNum, 10);
		$config = $wire->wire('config');

		$page->body .= $config->twig->render('mar/armain/spm/list/page.twig', ['spm' => $spm, 'people' => $people]);
		$page->body .= $config->twig->render('util/paginator/propel.twig', ['pager' => $people]);
		return $page->body;
	}
}
