<?php namespace Controllers\Mar;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use SalesPerson;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Spm as SpmManager;
// Dplus Validators
use Dplus\Filters\Mar\SalesPerson as FilterSalesPerson;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Spm extends AbstractController {
	private static $spm;

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
		$salesrep = self::getSpm()->get_create($data->id);
		self::pw('page')->js .= self::pw('config')->twig->render('mar/armain/spm/form/.js.twig');
		return self::salespersonDisplay($data, $salesrep);
	}

	private static function salespersonDisplay($data, SalesPerson $salesrep) {
		$spm  = self::getSpm();
		$html =  self::lockUser($spm, $salesrep);
		$html .= self::pw('config')->twig->render('mar/armain/spm/form/page.twig', ['person' => $salesrep, 'spm' => $spm]);
		return $html;
	}

	private static function lockUser(SpmManager $spm, SalesPerson $person) {
		$spm = self::getSpm();
		$html = '';

		if ($spm->recordlocker->function_locked($person->id) && !$spm->recordlocker->function_locked_by_user($person->id)) {
			$msg = "Sales Person $person->id is being locked by " . $spm->recordlocker->get_locked_user($person->id);
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Saless Person $person->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= '<div class="mb-3"></div>';
		} elseif (!$spm->recordlocker->function_locked($person->id)) {
			$spm->recordlocker->create_lock($person->id);
		}
		return $html;
	}

	public static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$spm  = self::getSpm();
		$spm->recordlocker->remove_lock();

		$filter = new FilterSalesPerson();
		$filter->init();

		if ($data->q) {
			$page->headline = "SPM: Searching '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby($page);
		$reps = $filter->query->paginate(self::pw('input')->pageNum, self::pw('session')->display);
		$page->js .= self::pw('config')->twig->render('mar/armain/spm/list/.js.twig');
		return self::listDisplay($data, $reps);
	}

	private static function listDisplay($data, PropelModelPager $reps) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('mar/armain/spm/list/page.twig', ['spm' => self::pw('modules')->get('Spm'), 'people' => $reps]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $reps]);
		return $html;
	}

	public static function getSpm() {
		if (empty(self::$spm)) {
			self::$spm = self::pw('modules')->get('Spm');
		}
		return self::$spm;
	}
}
