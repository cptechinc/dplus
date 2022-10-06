<?php namespace Controllers\Msa\Noce;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use ProspectSource;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Qnotes\Noce as Qnotes;
// Controllers
use Controllers\Msa\Base;
use Controllers\Msa\Noce;

class Ajax extends Noce {
	const DPLUSPERMISSION = '';
	const SHOWONPAGE = 10;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;
		return self::list($data);
	}


	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\NotePreDefined();
		$filter->filterSummarized();

		$page->headline = "Pre-defined Notes";

		if (strlen($data->q) > 0) {
			$filter->search($data->q);
			$page->headline = "NOCE: Searching for '$data->q'";
		}

		$filter->query->orderBy(\NotePreDefined::aliasproperty('id'));
		$notes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$html = self::displayList($data, $notes);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */


/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $notes) {
		$config = self::pw('config');
		$qnotes = self::getQnotes();

		$html  = '';
		$html .= $config->twig->render('msa/noce/ajax/form.twig');
		$html .= $config->twig->render('msa/noce/ajax/list.twig', ['qnotes' => $qnotes, 'notes' => $notes]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {

	}

/* =============================================================
	Supplemental
============================================================= */
}
