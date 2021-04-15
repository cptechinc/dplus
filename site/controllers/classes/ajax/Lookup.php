<?php namespace Controllers\Ajax;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters\AbstractFilter    as Filter;
use Dplus\Filters\Misc\PhoneBook    as PhoneBookFilter;
use Dplus\Filters\Misc\CountryCode  as CountryCodeFilter;
use Dplus\Filters\Mpo\PurchaseOrder as PurchaseOrderFilter;
use Dplus\Filters\Mgl\GlCode        as GlCodeFilter;
use Dplus\Filters\Min\ItemGroup     as ItemGroupFilter;
use Dplus\Filters\Mar\Customer      as CustomerFilter;
use Dplus\Filters\Map\Vendor        as VendorFilter;
use Dplus\Filters\Map\Vxm           as VxmFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
		$filter = new VxmFilter();
		$page->headline = "VXM";
		self::filterResults($filter, $wire, $data);
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
		$filter = new VendorFilter();
		$filter->init();
		$page->headline = "Vendors";
		self::filterResults($filter, $data);
	}

	/**
	 * Search Vendor Contacts
	 * @param  object $data
	 *                     vendorID  Vendor ID
	 *                     q         Search Term
	 * @return void
	 */
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
	 * Search Item Groups
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function itemGroups($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$data = self::sanitizeParametersShort($data, ['vendorID|text']);
		$page = self::pw('page');
		$filter = new ItemGroupFilter();
		$filter->init();
		$page->headline = "Item Groups";
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

	/**
	 * filter Purchase Orders
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function purchaseOrders($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$filter = new PurchaseOrderFilter();
		$filter->init();
		$wire->wire('page')->headline = "Purchase Orders";
		self::pw('config')->po = self::pw('modules')->get('ConfigurePo')->config();
		self::filterResults($filter, $data);
	}

	/**
	 * Filter General Ledger Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function generalLedgerCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$filter = new GlCodeFilter();
		$filter->init();
		$wire->wire('page')->headline = "General Ledger Codes";
		self::filterResults($filter, $data);
	}

	/**
	 * Search Customers
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function customers($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = new CustomerFilter();
		$filter->init();
		$filter->user(self::pw('user'));
		$page->headline = "Customers";
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		self::filterResults($filter, $wire, $data);
	}

	/**
	 * Search Country Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function countryCodes($data) {
		$data = self::sanitizeParameters($data, self::FIELDS_LOOKUP);
		$wire = self::pw();
		$page = $wire->wire('page');
		$filter = new CountryCodeFilter();
		$filter->init();
		$page->headline = "Country Codes";
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		self::filterResults($filter, $wire, $data);
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
		$path = $input->urlSegment(count($input->urlSegments()));
		self::filterResultsTwig($path, $filter->get_query(), $data->q);
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
		$path = $input->urlSegment(count($input->urlSegments()));
		$path = rtrim(str_replace($page->url, '', self::pw('input')->url()), '/');
		$path = preg_replace('/page\d+/', '', $path);
		self::filterResultsTwig($path, $filter->query, $data->q);
	}

	private static function filterResultsTwig($path = 'codes', BaseQuery $query, $q = '') {
		$input = self::pw('input');
		$page  = self::pw('page');
		$results = $query->paginate($input->pageNum, 10);
		$page->body .= self::pw('config')->twig->render("api/lookup/$path/search.twig", ['results' => $results, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $q]);
		$page->body .= '<div class="mb-3"></div>';
		$page->body .= self::pw('config')->twig->render('util/paginator.twig', ['resultscount'=> $results->getNbResults() != $query->count() ? $query->count() : $results->getNbResults()]);
	}
}
