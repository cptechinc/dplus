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
use Controllers\Mci\Ci\Ci;

class Customer extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['custID|string']);

		if (empty($data->custID)) {
			self::pw('page')->js .= self::pw('config')->twig->render('quotes/customer/customer-form.js.twig');
			return self::customerForm($data);
		}

		$validate = new MarValidator();
		if ($validate->custid($data->custID) === false) {
			self::pw('page')->headline = "Customer $data->custID not found";
			$html  = self::invalidCustid($data);
			$html .= self::customerForm($data);
			return $html;
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
		self::initHooks();
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
		$html .= $config->twig->render('quotes/customer/search-form.twig', ['input' => self::pw('input')]);
		$html .= $config->twig->render('quotes/customer/quotes-list-links.twig', ['quotes' => $quotes, 'quotepage' => self::pw('pages')->get('pw_template=quote-view')->url]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $quotes]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	public static function listUrl($custID, $shiptoID = '', $qnbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=quotes')->url);
		$url->path->add('customer');
		$url->query->set('custID', $custID);
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}

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
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMqo');

		$m->addHook('Page(pw_template=quotes)::ciUrl', function($event) {
			$event->return = Ci::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quotes)::ciShiptoUrl', function($event) {
			$event->return = Ci::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=quotes)::custQuotesUrl', function($event) {
			$event->return = self::listUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		$m->addHook('Page(pw_template=quotes)::quoteUrl', function($event) {
			$event->return = self::quoteUrl($event->arguments(0));
		});
	}
}
