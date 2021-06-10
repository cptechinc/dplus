<?php namespace Controllers\Mar;
// Dplus Model
use SalesPerson;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Spm as SpmManager;
// Dplus Validators
use Dplus\Filters\Mar\SalesPerson as FilterSalesPerson;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
			$spm = self::pw('modules')->get('Spm');
			$spm->process_input($input);
		}

		self::pw('session')->redirect(self::pw('page')->redirectURL(), $http301 = false);
	}

	public static function salesperson($data) {
		$data = self::sanitizeParametersShort($data, ['id|text', 'action|text']);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		$page = self::pw('page');
		$spm  = self::pw('modules')->get('Spm');
		$person = $spm->get_create($data->id);
		self::lockUser($spm, $person);
		$page->body .= self::pw('config')->twig->render('mar/armain/spm/form/page.twig', ['person' => $person, 'spm' => $spm]);
		$page->js   .= self::pw('config')->twig->render('mar/armain/spm/form/.js.twig');
	}

	private static function lockUser(SpmManager $spm, SalesPerson $person) {
		$page = self::pw('page');
		if ($spm->recordlocker->function_locked($person->id) && !$spm->recordlocker->function_locked_by_user($person->id)) {
			$msg = "Sales Person $person->id is being locked by " . $spm->recordlocker->get_locked_user($person->id);
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Saless Person $person->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$page->body .= $html->div('class=mb-3');
		} elseif (!$spm->recordlocker->function_locked($person->id)) {
			$spm->recordlocker->create_lock($person->id);
		}
		return $page->body;
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
		$people = $filter->query->paginate($wire->wire('input')->pageNum, $wire->wire('session')->display);
		$config = $wire->wire('config');

		$page->body .= $config->twig->render('mar/armain/spm/list/page.twig', ['spm' => $spm, 'people' => $people]);
		$page->body .= $config->twig->render('util/paginator/propel.twig', ['pager' => $people]);
		$page->js   .= $config->twig->render('mar/armain/spm/list/.js.twig');
		return $page->body;
	}
}
