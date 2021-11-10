<?php namespace Controllers\Ajax;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters;
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
	const FIELDS_LOOKUP = ['q|text'];

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
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Tariff Codes";
		$filter = self::pw('modules')->get('FilterInvTariffCodes');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * Search MSDS Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function msdsCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Msds Codes";
		$filter = self::pw('modules')->get('FilterInvMsdsCodes');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * Search Freight Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function freightCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Freight Codes";
		$filter = self::pw('modules')->get('FilterMsoFreightCodes');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * Search VXM
	 * @param  object $data
	 *                     vendorID Vendor ID
	 *                     q        Search Term
	 * @return void
	 */
	public static function vxm($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "VXM";
		$filter = new VxmFilter();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Warehouses
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function warehouses($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Warehouses";
		$filter = self::pw('modules')->get('FilterWarehouses');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * Search VXM
	 * @param  object $data
	 *                     whseID Warehouse ID
	 *                     q        Search Term
	 * @return void
	 */
	public static function warehouseBins($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['whseID|text']);
		self::pw('page')->headline = "Warehouse Bins";
		$filter = new Filters\Min\WarehouseBin();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Users
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function users($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Users";
		$filter = self::pw('modules')->get('FilterDplusUsers');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * Search Vendors
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function vendors($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Vendors";;
		$filter = new VendorFilter();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Vendor Contacts
	 * @param  object $data
	 *                     vendorID  Vendor ID
	 *                     q         Search Term
	 * @return void
	 */
	public static function vendorContacts($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['vendorID|text']);
		self::pw('page')->headline = "Vendor Contacts";
		$filter = new PhoneBookFilter();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Vendor Ship-Froms
	 * @param  object $data
	 *                     vendorID  Vendor ID
	 *                     q         Search Term
	 * @return void
	 */
	public static function vendorShipfroms($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['vendorID|text']);
		self::pw('page')->headline = "Vendor Ship-Froms";
		$filter = new Filters\Map\VendorShipfrom();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Item Groups
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function itemGroups($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['vendorID|text']);
		self::pw('page')->headline = "Item Groups";
		$filter = new ItemGroupFilter();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Items
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function itmItems($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Item Master";
		$filter = self::pw('modules')->get('FilterItemMaster');
		$filter->init_query();
		return self::moduleFilterResults($filter, $data);
	}

	/**
	 * filter Purchase Orders
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function purchaseOrders($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Purchase Orders";
		$filter = new PurchaseOrderFilter();
		$filter->init();
		self::pw('config')->po = self::pw('modules')->get('ConfigurePo')->config();
		return self::filterResults($filter, $data);
	}

	/**
	 * Filter General Ledger Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function generalLedgerCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "General Ledger Codes";
		$filter = new GlCodeFilter();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Customers
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function customers($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		$page = self::pw('page');
		$filter = new CustomerFilter();
		$filter->init();
		$filter->user(self::pw('user'));
		$page->headline = "Customers";
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Country Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function countryCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		$page = self::pw('page');
		$filter = new CountryCodeFilter();
		$filter->init();
		$page->headline = "Country Codes";
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search DCM (PrWorkCenter) Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function dcmCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		$page = self::pw('page');
		$filter = new Filters\Mpm\PrWorkCenter();
		$filter->init();
		$page->headline = "Work Center Codes";
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Sysop Codes
	 * @param  object $data
	 *                     q      Search Term
	 *                     system Sysop System
	 * @return void
	 */
	public static function sysopCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['system|text']);
		$page = self::pw('page');
		$filter = new Filters\Msa\MsaSysopCode();
		$filter->init();
		$page->headline = "System Optional Codes";
		if ($data->system) {
			$filter->system($data->system);
		}
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Sysop Options
	 * @param  object $data
	 *                     q      Search Term
	 *                     system Sysop System
	 *                     sysop  Sysop Optional Code
	 * @return void
	 */
	public static function sysopOptions($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['system|text', 'sysop|text']);

		$filter = new Filters\Msa\SysopOptionalCode();
		$filter->init();
		$page = self::pw('page');
		$page->headline = "Optional Code ($data->sysop) Options";
		if ($data->system) {
			$filter->system($data->system);
		}
		if ($data->sysop) {
			$filter->query->filterBySysop($data->sysop);
		}
		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for $data->q";
		}
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Printers
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function printers($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Printers";;
		$filter = new Filters\Misc\Printer();
		$filter->init();
		return self::filterResults($filter, $data);
	}

	private static function moduleFilterResults(Module $filter, $data) {
		$input = self::pw('input');
		$page  = self::pw('page');
		$filter->filter_input(self::pw('input'));

		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for '$data->q'";
		}
		$filter->apply_sortby($page);
		$path = $input->urlSegment(count($input->urlSegments()));
		return self::filterResultsTwig($path, $filter->get_query(), $data->q);
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
		return self::filterResultsTwig($path, $filter->query, $data->q);
	}

	private static function filterResultsTwig($path = 'codes', BaseQuery $query, $q = '') {
		$input = self::pw('input');
		$results = $query->paginate($input->pageNum, 10);
		$twigpath = "api/lookup/codes/search.twig";

		if (self::pw('config')->twigloader->exists("api/lookup/$path/search.twig")) {
			$twigpath = "api/lookup/$path/search.twig";
		}

		$html  = '';
		$html .= self::pw('config')->twig->render("$twigpath", ['results' => $results, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $q]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::pw('config')->twig->render('util/paginator.twig', ['resultscount'=> $results->getNbResults() != $query->count() ? $query->count() : $results->getNbResults()]);
		return $html;
	}
}
