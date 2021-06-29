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

		if ($data->print) {
			// self::pw('session')->redirect(self::orderPrintUrl($data->qnbr), $http301 = false);
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
		$twig['items']       = $config->twig->render("quotes/quote/quote-items.twig", ['config' => $config, 'quote' => $quote]);
		$twig['actions']     = $config->twig->render('quotes/quote/quote-actions.twig', ['user' => $user, 'quote' => $quote]);
		$twig['qnotes']      = $config->twig->render('quotes/quote/quote-notes.twig', ['qnbr' => $qnbr, 'qnotes_qt' => self::pw('modules')->get('QnotesQt')]);
		$twig['documents']   = $config->twig->render('quotes/quote/quote-documents.twig', ['documents' => $docm->getDocuments($qnbr), 'qnbr' => $qnbr]);
		$twig['useractions'] = self::quoteUserActionsDisplay($data);
		return $config->twig->render('quotes/quote/page.twig', ['html' => $twig]);
	}

	private static function quoteUserActionsDisplay($data) {
		$filterUserActions = self::pw('modules')->get('FilterUserActions');
		$query = $filterUserActions->get_actionsquery($input);
		$actions = $query->filterByQuotelink($data->qnbr)->find();
		return self::pw('config')->twig->render('quotes/quote/user-actions.twig', ['module_useractions' => $filterUserActions, 'actions' => $actions, 'qnbr' => $data->qnbr]);
	}


	/**
	 * Render Twig Elements fo Sales Order
	 * @param  ActiveRecordInterface|SalesOrder|SalesHistory $quote  [description]
	 * @param  stdClass              $data
	 * @param  Module                $qnotes
	 * @param  array                 $twig   Twig array to append to
	 * @return array
	 */
	private static function _orderDetails(ActiveRecordInterface $quote, $data, Module $qnotes, array $twig) {
		$data = self::sanitizeParametersShort($data, ['qnbr|qnbr']);
		$validate = self::validator();

		if ($validate->order($data->qnbr) === false && $validate->invoice($data->qnbr) === false) {
			return self::invalidQt($data);
		}
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$page    = self::pw('page');
		$docm    = self::docm();
		$documents = $docm->getDocuments($data->qnbr);

		$module_useractions = $modules->get('FilterUserActions');
		$query_useractions = $module_useractions->get_actionsquery(self::pw('input'));
		$actions = $query_useractions->filterBySalesorderlink($data->qnbr)->find();

		$twig['tracking']    = $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['order' => $quote, 'urlmaker' => $modules->get('DplusURLs')]);
		$twig['documents']   = $config->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents, 'docm' => $docm, 'qnbr' => $data->qnbr]);
		$twig['qnotes']      = $config->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'qnbr' => $data->qnbr]);
		$twig['useractions'] = $config->twig->render('sales-orders/sales-order/user-actions.twig', ['module_useractions' => $module_useractions, 'actions' => $actions, 'qnbr' => $data->qnbr]);
		$twig['modals']      = $config->twig->render('sales-orders/sales-order/specialorder-modal.twig', ['qnbr' => $data->qnbr]);
		$page->js   .= $config->twig->render('sales-orders/sales-order/specialorder-modal.js.twig', ['qnbr' => $data->qnbr]);
		return $twig;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMqo');

		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderUrl', function($event) {
		// 	$event->return = self::orderUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view)::orderListUrl', function($event) {
		// 	$event->return = self::orderListUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderPrintUrl', function($event) {
		// 	$event->return = self::orderPrintUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderEditUrl', function($event) {
		// 	$event->return = self::orderEditUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderEditUnlockUrl', function($event) {
		// 	$event->return = self::orderEditUnlockUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderNotesUrl', function($event) {
		// 	$event->return = self::orderNotesUrl($event->arguments(0), $event->arguments(1));
		// });
		//
		// $m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::iiUrl', function($event) {
		// 	$event->return = Ii::iiUrl($event->arguments(0));
		// });
	}
}
