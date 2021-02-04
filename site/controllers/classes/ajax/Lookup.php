<?php namespace Controllers\Ajax;

use ProcessWire\Module, ProcessWire\ProcessWire;
use Mvc\Controllers\AbstractController;

use Dplus\Filters\AbstractFilter as Filter;
use Dplus\Filters\Misc\PhoneBook as PhoneBookFilter;

class Lookup extends AbstractController {
	const FIELDS_LOOKUP = ['q' => ['sanitizer' => 'text']];
	public static function test() {
		return 'test';
	}

	/**
	 * Search Tariff Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function tariffCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterInvTariffCodes');
		$filter->init_query();
		$page->headline = "Tariff Codes";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search MSDS Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function msdsCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterInvMsdsCodes');
		$filter->init_query();
		$page->headline = "Msds Codes";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search Freight Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function freightCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterMsoFreightCodes');
		$filter->init_query();
		$page->headline = "Freight Codes";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search VXM
	 * @param  object $data
	 *                     vendorID Vendor ID
	 *                     q        Search Term
	 * @return void
	 */
	public static function vxm($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterXrefItemVxm');
		$filter->init_query();
		$page->headline = "VXM";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search Warehouses
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function warehouses($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterWarehouses');
		$filter->init_query();
		$page->headline = "Warehouses";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search Users
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function users($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterDplusUsers');
		$filter->init_query();
		$page->headline = "Users";
		self::moduleFilterResults($filter, $wire, $data);
	}

	/**
	 * Search Vendors
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function vendors($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = $wire->wire('modules')->get('FilterVendors');
		$filter->init_query(self::pw('user'));
		$page->headline = "Users";
		self::moduleFilterResults($filter, $wire, $data);
	}

	public static function vendorContacts($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		$page = self::pw('page');
		$filter = new PhoneBookFilter();
		$filter->init();
		$page->headline = "Vendor Contacts";
		self::filterResults($filter, $data);
	}

	/**
	 * Search Items
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function itmItems($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$filter = $wire->wire('modules')->get('FilterItemMaster');
		$filter->init_query();
		$wire->wire('page')->headline = "Item Master";
		self::moduleFilterResults($filter, $wire, $data);
	}

	private static function moduleFilterResults(Module $filter, ProcessWire $wire, $data) {
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
		$page->body .= $wire->wire('config')->twig->render('util/paginator.twig', ['resultscount'=> $results->getNbResults() != $query->count() ? $query->count() : $results->getNbResults()]);
	}

	private static function filterResults(Filter $filter, $data) {
		$input = self::pw('input');
		$page  = self::pw('page');
		$filter->filterInput(self::pw('input'));

		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for '$data->q'";
		}

		$filter->sortby($page);
		$query   = $filter->query;
		$results = $query->paginate($input->pageNum, 10);

		$path = $input->urlSegment(count($input->urlSegments()));

		$path = rtrim(str_replace($page->url, '', self::pw('input')->url()), '/');
		$page->body .= self::pw('config')->twig->render("api/lookup/$path/search.twig", ['results' => $results, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $data->q]);
		$page->body .= self::pw('config')->twig->render('util/paginator.twig', ['resultscount'=> $results->getNbResults() != $query->count() ? $query->count() : $results->getNbResults()]);
	}
}
