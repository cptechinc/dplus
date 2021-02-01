<?php namespace Controllers\Ajax;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

class Lookup extends AbstractController {
	const FIELDS_LOOKUP = ['q' => ['sanitizer' => 'text']];
	public static function test() {
		return 'test';
	}

	public static function tariffCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterInvTariffCodes');
		$filter->init_query();
		$filter->filter_input($wire->wire('input'));
		$page->headline = "Tariff Codes";
		self::filterResults($filter, $wire, $data);
	}

	public static function itmItems($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$filter = $wire->wire('modules')->get('FilterItemMaster');
		$filter->init_query();
		$wire->wire('page')->headline = "Item Master";
		self::filterResults($filter, $wire, $data);
	}

	private static function filterResults(Module $filter, ProcessWire $wire, $data) {
		$input = $wire->wire('input');
		$page = $wire->wire('page');
		$filter->filter_input($wire->wire('input'));

		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for '$data->q'";
		}
		$filter->apply_sortby($page);
		$query = $filter->get_query();

		$results = $query->paginate($input->pageNum, 10);

		$path = $input->urlSegment(count($input->urlSegments()));
		$page->body .= $wire->wire('config')->twig->render("api/lookup/$path/search.twig", ['results' => $results, 'datamatcher' => $wire->wire('modules')->get('RegexData'), 'q' => $data->q]);
		$page->body .= $wire->wire('config')->twig->render('util/paginator.twig', ['resultscount'=> $results->getNbResults()]);
	}
}
