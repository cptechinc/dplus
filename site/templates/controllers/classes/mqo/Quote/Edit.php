<?php namespace Controllers\Mqo\Quote;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use QuoteQuery, Quote as QtModel;
use CustomerQuery, Customer;
use Quothed;
// Dplus Configs
use Dplus\Configs;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Edit extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['qnbr|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->qnbr) === false) {
			return self::quote($data);
		}

		return self::lookupForm();
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'qnbr|text']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::quoteEditUrl($data->qnbr), $http301 = false);
		}

		if ($data->action) {
			$page = self::pw('page');
			$eqo  = self::getEqo($data->qnbr);
			$eqo->process_input(self::pw('input'));

			$url = self::quoteEditUrl($data->qnbr);
			if (in_array($data->action, ['exit']) || isset($data->exit)) {
				$url = self::quoteUrl($data->qnbr);
			}
			self::pw('session')->redirect($url, $http301 = false);
		}
		self::pw('session')->redirect(self::pw('input')->url(), $http301 = false);
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

		self::pw('page')->headline = "Editing Quote #$data->qnbr";

		$eqo = self::getEqo($data->qnbr);

		if ($eqo->hasEditableQuote() === false) {
			$eqo->requestEditableQuote();
		}
		self::initHooks();
		return self::display($data);
	}

	private static function display($data) {
		$eqo = self::getEqo($data->qnbr);

		$html  = '';
		$html .= self::displayHeader($data);
		$html .= self::headerForm($data);
		return $html;
	}

	private static function displayHeader($data) {
		$htmlWriter = self::pw('modules')->get('HtmlWriter');
		$quote      = QuoteQuery::create()->findOneByQuoteid($data->qnbr);
		$customer   = CustomerQuery::create()->findOneByCustid($quote->custid);
		$twig = self::pw('config')->twig;


		$links = $twig->render('quotes/quote/edit/links-header.twig', ['user' => self::pw('user'), 'quote' => $quote]);
		$header = $twig->render('quotes/quote/edit/header.twig', ['customer' => $customer, 'quote' => $quote]);

		$html = '';
		$html .= $htmlWriter->div('class=mb-3', $links);
		$html .= $htmlWriter->div('class=mb-3', $header);
		return $html;
	}

	private static function headerForm($data) {
		$eqo = self::getEqo($data->qnbr);
		$quote = $eqo->getEditableQuote();
		$customer = CustomerQuery::create()->findOneByCustid($quote->custid);
		return self::pw('config')->twig->render('quotes/quote/edit/header/form.twig', ['quote' => $quote, 'states' => $eqo->getStates(), 'shipvias' => $eqo->getShipvias(), 'warehouses' => $eqo->getWarehouses(), 'shiptos' => $customer->get_shiptos()]);
	}

/* =============================================================
	URL functions
============================================================= */
	public static function quoteUnlockUrl($qnbr) {
		return self::quoteUrl($qnbr);
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function getEqo($qnbr = '') {
		$eqo = self::pw('modules')->get('Eqo');
		if ($qnbr) {
			$eqo->setQnbr($qnbr);
		}
		return $eqo;
	}


	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMqo');

		$m->addHook('Page(pw_template=quote-view)::quoteUnlockUrl', function($event) {
			$event->return = self::quotePrintUrl($event->arguments(0));
		});
	}
}
