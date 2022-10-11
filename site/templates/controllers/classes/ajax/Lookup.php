<?php namespace Controllers\Ajax;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\AbstractFilter    as Filter;
use Dplus\Filters\Misc\PhoneBook    as PhoneBookFilter;
use Dplus\Filters\Mpo\PurchaseOrder as PurchaseOrderFilter;
use Dplus\Filters\Mar\Customer      as CustomerFilter;
use Dplus\Filters\Map\Vendor        as VendorFilter;
use Dplus\Filters\Map\Vxm           as VxmFilter;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Lookup extends Controller {
	const FIELDS_LOOKUP = ['q|text'];

	public static function test() {
		return 'test';
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
		$data->addSortColumns = [\Vendor::aliasproperty('id')];
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
		self::sanitizeParametersShort($data, ['vendorID|string']);
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
		self::sanitizeParametersShort($data, ['vendorID|string']);
		self::pw('page')->headline = "Vendor Ship-Froms";
		$filter = new Filters\Map\VendorShipfrom();
		$filter->init();
		return self::filterResults($filter, $data);
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
		$filter->sort(self::pw('input')->get);
	
		if ($data->has('addSortColumns')) {
			$filter->query->orderBy($data->addSortColumns[0]);
		}
		
		$path = $input->urlSegment(count($input->urlSegments()));
		$path = rtrim(str_replace($page->url, '', self::pw('input')->url()), '/');
		$path = preg_replace('/page\d+/', '', $path);
		return self::filterResultsTwig($path, $filter->query, $data->q);
	}

	private static function filterResultsTwig($path = 'codes', BaseQuery $query, $q = '') {
		$input = self::pw('input');
		$results = $query->paginate($input->pageNum, 10);
		$query->find();
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
