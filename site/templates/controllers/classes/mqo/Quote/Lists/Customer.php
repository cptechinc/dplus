<?php namespace Controllers\Mqo\Quote\Lists;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// Dplus Model
use QuoteQuery, Quote as QtModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Code Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Dplus Filters
use Dplus\Filters\Mqo\Quote as FilterQuotes;
// Mvc Controllers
use Controllers\Mqo\Quote\Base;

class Customer extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['custID|text']);


		if (empty($data->custID)) {
			self::pw('page')->js .= self::pw('config')->twig->render('quotes/customer/customer-form.js.twig');
			return self::customerForm($data);
		}

		$validate = new MarValidator();
		if ($validate->custid($data->custID) === false) {
			self::pw('page')->headline = "Customer $data->custID not found";
			$html =  self::invalidCustid($data);
		}

		return self::listQuotes($data);
	}

	public static function listQuotes($data) {
		self::pw('page')->headline = "Quotes for Customer $data->custID";
		$filter = new FilterQuotes();
		$filter->user(self::pw('user'));
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$quotes = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($quotes);
	}

/* =============================================================
	Displays
============================================================= */
	public static function invalidCustid($data) {
		$html = self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Customer Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID not found"]);
		return $html;
	}

	public static function customerForm($data) {
		$html = self::pw('config')->twig->render('quotes/customer/customer-form.twig');
		return $html;
	}

	public static function displayList(ModelPager $quotes) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('quotes/search-form.twig', ['input' => self::pw('input')]);
		$html .= $config->twig->render('quotes/quotes-list-links.twig', ['quotes' => $quotes, 'quotepage' => self::pw('pages')->get('pw_template=quote-view')->url]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $quotes]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	public static function listUrl($custID, $qnbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=quotes')->url);
		$url->path->add('customer');
		$url->query->set('custID', $custID);

		if ($qnbr) {
			$filter = new FilterQuotes();

			if ($filter->exists($qnbr)) {
				$url->query->set('focus', $qnbr);
				$offset = $filter->positionQuick($qnbr);
				$pagenbr = self::getPagenbrFromOffset($offset);
				$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=quotes')->name, $pagenbr);
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() { // TODO HOOKS for CI
		$m = self::pw('modules')->get('DpagesMso');
	}
}
