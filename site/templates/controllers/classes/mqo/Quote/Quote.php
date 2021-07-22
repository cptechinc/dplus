<?php namespace Controllers\Mqo\Quote;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use QuoteQuery, Quote as QtModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Configs
use Dplus\Configs;
// Dplus Classes
use Dplus\CodeValidators\Mqo as MqoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\Ii;
use Controllers\Mqo\Quote\Lists\Customer as CustomerQuotes;

class Quote extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['qnbr|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->qnbr) === false) {
			return self::quote($data);
		}
		return self::lookupScreen($data);
	}

/* =============================================================
	Displays
============================================================= */
	public static function quote($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|text', 'print|bool']);
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		if ($validate->quoteAccess($data->qnbr, self::pw('user')) === false) {
			return self::qtAccessDenied($data);
		}

		self::pw('page')->headline = "Quote #$data->qnbr";
		self::pw('page')->listpage = self::pw('pages')->get('pw_template=quotes');

		$quote = QuoteQuery::create()->filterByQuotenumber($data->qnbr)->findOne();
		return self::quoteDisplay($data, $quote);
	}

	private static function quoteDisplay($data, QtModel $quote) {
		$config = self::pw('config');
		$docm   = self::docm();
		$twig = [];
		$twig['header']      = $config->twig->render("quotes/quote/quote-page.twig", ['quote' => $quote, 'document_management' => $docm]);
		$twig['items']       = $config->twig->render("quotes/quote/quote-items.twig", ['configSo' => Configs\So::config(), 'quote' => $quote]);
		$twig['actions']     = $config->twig->render('quotes/quote/quote-actions.twig', ['user' => self::pw('user'), 'quote' => $quote]);
		$twig['qnotes']      = $config->twig->render('quotes/quote/quote-notes.twig', ['qnbr' => $data->qnbr, 'qnotes_qt' => self::pw('modules')->get('QnotesQt')]);
		$twig['documents']   = $config->twig->render('quotes/quote/quote-documents.twig', ['documents' => $docm->getDocuments($data->qnbr), 'qnbr' => $data->qnbr]);
		$twig['useractions'] = self::quoteUserActionsDisplay($data);
		return $config->twig->render('quotes/quote/page.twig', ['html' => $twig]);
	}

	private static function quoteUserActionsDisplay($data) {
		$filterUserActions = self::pw('modules')->get('FilterUserActions');
		$query = $filterUserActions->get_actionsquery(self::pw('input'));
		$actions = $query->filterByQuotelink($data->qnbr)->find();
		return self::pw('config')->twig->render('quotes/quote/user-actions.twig', ['module_useractions' => $filterUserActions, 'actions' => $actions, 'qnbr' => $data->qnbr]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMqo');

		$m->addHook('Page(pw_template=quote-view)::ciUrl', function($event) {
			$event->return = CustomerQuotes::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::ciShiptoUrl', function($event) {
			$event->return = CustomerQuotes::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=quote-view)::iiUrl', function($event) {
			$event->return = CustomerQuotes::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::iiUrl', function($event) {
			$event->return = Ii::iiUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::qnotesUrl', function($event) {
			$event->return = self::quoteNotesUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=quote-view)::quoteEditUrl', function($event) {
			$event->return = self::quoteEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::orderQuoteUrl', function($event) {
			$event->return = self::orderQuoteUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::documentsUrl', function($event) {
			$event->return = self::documentsUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::documentUrl', function($event) {
			$event->return = self::documentUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		$m->addHook('Page(pw_template=quote-view)::quotePrintUrl', function($event) {
			$event->return = self::quotePrintUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::quoteListUrl', function($event) {
			$event->return = self::quoteListUrl($event->arguments(0));
		});
	}
}
