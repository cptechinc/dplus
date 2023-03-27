<?php namespace Controllers\Mqo\Quote;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use QuoteQuery, Quote as QtModel;
use CustomerQuery, Customer;
use Quothed as EditableQuote;
// Dplus Configs
use Dplus\Configs;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mso\SalesOrder;

class OrderQuote extends Edit {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['qnbr|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->qnbr) === false) {
			return self::quote($data);
		}

		return self::lookupForm();
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'qnbr|text']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::orderQuoteUrl($data->qnbr), $http301 = false);
		}

		if ($data->action) {
			$eqo  = self::getEqo($data->qnbr);
			$eqo->processInput(self::pw('input'));
			$url = self::orderQuoteUrl($data->qnbr);
			if ($data->action == 'create-order') {
				$url = SalesOrder\SalesOrder::orderEditNewUrl();
			}
			self::pw('session')->redirect($url, $http301 = false);
		}
		self::pw('session')->redirect(self::pw('input')->url(), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function setPageTitle($data) {
		self::pw('page')->headline = "Ordering Quote #$data->qnbr";
	}

	protected static function quoteJs($data) {
		$config = self::pw('config');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/quotes/order-quote.js'));
	}

	protected static function display($data) {
		$eqo = self::getEqo($data->qnbr);
		$quote = $eqo->getEditableQuote();

		$html  = '';
		$html .= self::displayHeader($data);
		$html .= self::items($data, $quote);
		return $html;
	}

	private static function displayHeader($data) {
		$htmlWriter = self::pw('modules')->get('HtmlWriter');
		$quote      = QuoteQuery::create()->findOneByQuoteid($data->qnbr);
		$customer   = CustomerQuery::create()->findOneByCustid($quote->custid);
		$twig = self::pw('config')->twig;

		$links = $twig->render('quotes/quote/order/links-header.twig', ['user' => self::pw('user'), 'quote' => $quote]);
		$header = $twig->render('quotes/quote/edit/header.twig', ['customer' => $customer, 'quote' => $quote]);

		$html = '';
		$html .= $htmlWriter->div('class=mb-3', $links);
		$html .= $htmlWriter->div('class=mb-3', $header);
		return $html;
	}


	private static function items($data, EditableQuote $quote) {
		$eqo = self::getEqo($data->qnbr);
		$htmlWriter = self::pw('modules')->get('HtmlWriter');

		$html = '';
		$html .= self::pw('config')->twig->render('quotes/quote/order/items.twig', ['quote' => $quote, 'eqo' => $eqo]);
		return $html;
	}

/* =============================================================
	URL functions
============================================================= */

/* =============================================================
	Supplemental
============================================================= */

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMqo');

		$m->addHook('Page(pw_template=quote-view)::quoteUnlockUrl', function($event) {
			$event->return = self::quoteUnlockUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::deleteItemUrl', function($event) {
			$event->return = self::removeItemUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
