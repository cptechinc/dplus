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
// Dplus Filters
use Dplus\Filters\Mqo\Quote as FilterQuotes;
// Mvc Controllers
use Controllers\Mqo\Quote\Base;

class Quote extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		return self::listQuotes($data);
	}

	public static function listQuotes($data) {
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


/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() { // TODO HOOKS for CI
		$m = self::pw('modules')->get('DpagesMso');
	}
}
