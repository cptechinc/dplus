<?php namespace Controllers\Msa;
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

class Noce extends Base {
	const DPLUSPERMISSION = 'noce';
	const SHOWONPAGE = 10;

	private static $qnotes;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		$fields = ['code|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url     = self::noceUrl();
		$qnotes  = self::getQnotes();

		if ($data->action) {
			$qnotes->processInput(self::pw('input'));
			$url  = self::noceUrl($data->code);
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$page   = self::pw('page');
		$filter = new Filters\Msa\NotePreDefined();
		$filter->filterSummarized();

		$page->headline = "Pre-defined Notes";

		if (empty($data->q) === false) {
			$filter->search($data->q);
			$page->headline = "NOCE: Searching for '$data->q'";
		}

		$filter->sortby($page);
		$notes = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		self::initHooks();

		$page->js .= self::pw('config')->twig->render('msa/noce/.js.twig', ['qnotes' => self::getQnotes()]);
		$html = self::displayList($data, $notes);
		self::getQnotes()->deleteResponse();
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function noceUrl($code = '') {
		if (empty($code)) {
			return Menu::noceUrl();
		}
		return self::noceFocusUrl($code);
	}

	public static function noceFocusUrl($focus) {
		$filter = new Filters\Msa\NotePreDefined();
		$filter->filterSummarized();
		if ($filter->exists($focus) === false) {
			return Menu::noceUrl();
		}
		$position = $filter->positionQuick($focus);
		$pagenbr = self::getPagenbrFromOffset($position, self::SHOWONPAGE);

		$url = new Purl(Menu::noceUrl());
		$url->query->set('focus', $focus);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, 'noce', $pagenbr);
		return $url->getUrl();
	}

	public static function notesDeleteUrl($code) {
		$url = new Purl(Menu::noceUrl());
		$url->query->set('code', $code);
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $notes) {
		$config = self::pw('config');
		$qnotes = self::getQnotes();

		$html  = '';
		// $html .= $config->twig->render('code-tables/msa/noce/bread-crumbs.twig');
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('msa/noce/list.twig', ['qnotes' => $qnotes, 'notes' => $notes]);
		$html .= $config->twig->render('msa/noce/notes-modal.twig', ['qnotes' => $qnotes]);
		return $html;
	}

	public static function displayResponse($data) {
		$qnotes = self::getQnotes();
		$response = $qnotes->getResponse();
		if (empty($response)) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('Dpages');

		$m->addHook('Page(template=test)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(template=test)::notesDeleteUrl', function($event) {
			$event->return = self::notesDeleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getQnotes() {
		if (empty(self::$qnotes)) {
			self::$qnotes = new Qnotes();
		}
		return self::$qnotes;
	}
}
