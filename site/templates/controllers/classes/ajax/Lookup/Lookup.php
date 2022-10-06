<?php namespace Controllers\Ajax\Lookup;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\AbstractFilter as Filter;
// Mvc Controllers
use Mvc\Controllers\Controller;

abstract class Lookup extends Controller {
	const FIELDS_LOOKUP = ['q|text'];

	public static function test() {
		return 'test';
	}

/* =============================================================
	Indexes
============================================================= */
	protected static function moduleFilterResults(Module $filter, $data) {
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

	protected static function filterResults(Filter $filter, $data) {
		$input = self::pw('input');
		$page  = self::pw('page');
		$filter->filterInput(self::pw('input'));

		if ($data->q) {
			$filter->search($data->q);
			$page->headline = "Searching for '$data->q'";
		}
		$filter->sort($input->get);
		if ($data->has('addSortColumns')) {
			$filter->query->orderBy($data->addSortColumns[0]);
		}
		$path = $input->urlSegment(count($input->urlSegments()));
		$path = rtrim(str_replace($page->url, '', self::pw('input')->url()), '/');
		$path = preg_replace('/page\d+/', '', $path);
		return self::filterResultsTwig($path, $filter->query, $data->q);
	}

	protected static function filterResultsTwig($path = 'codes', BaseQuery $query, $q = '') {
		$input = self::pw('input');
		$results = $query->paginate($input->pageNum, 10);
		$twigpath = "api/lookup/codes/search.twig";
		
		if (self::pw('config')->twigloader->exists("api/lookup/$path/search.twig")) {
			$twigpath = "api/lookup/$path/search.twig";
		}

		return self::displayResults($twigpath, $results, $q);
	}

	protected static function displayResults($path, PropelModelPager $results, $q = '') {
		$html  = '';
		$html .= self::pw('config')->twig->render("$path", ['results' => $results, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $q]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::pw('config')->twig->render('util/paginator/propel.twig', ['pager'=> $results]);
		return $html;
	}
}
