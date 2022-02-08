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

class Edit extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['qnbr|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->qnbr) === false) {
			return self::quote($data);
		}

		return self::lookupForm();
	}

	public static function editNewQuote($data) {
		$qnbr = self::pw('user')->get_lockedID();

		if (empty($qnbr)) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "New Sales Order # not found"]);
		}
		self::pw('session')->redirect(self::quoteEditUrl($qnbr), $http301 = false);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'qnbr|text']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::quoteEditUrl($data->qnbr), $http301 = false);
		}

		if ($data->action) {
			$page = self::pw('page');
			$eqo  = self::getEqo($data->qnbr);

			if ($data->action == 'edit-new-quote') {
				$qnbr = self::pw('user')->get_lockedID();
				self::pw('session')->redirect(self::quoteEditUrl($qnbr), $http301 = false);
			}
			$eqo->processInput(self::pw('input'));

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
		$data = self::sanitizeParametersShort($data, ['qnbr|text', 'print|bool', 'q|text']);
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		if ($validate->quoteAccess($data->qnbr, self::pw('user')) === false) {
			return self::qtAccessDenied($data);
		}

		static::setPageTitle($data);

		$eqo = self::getEqo($data->qnbr);

		if ($eqo->hasEditableQuote() === false) {
			$eqo->requestEditableQuote();
		}
		self::initHooks();
		static::quoteJs($data);
		return static::display($data);
	}

	protected static function quoteJs($data) {
		$eqo = self::getEqo($data->qnbr);
		$config = self::pw('config');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		self::pw('page')->js .= $config->twig->render('quotes/quote/edit/classes.js.twig');
		self::pw('page')->js .= $config->twig->render('quotes/quote/edit/.js.twig', ['eqo' => $eqo]);
	}

	protected static function setPageTitle($data) {
		self::pw('page')->headline = "Editing Quote #$data->qnbr";
	}

	protected static function display($data) {
		$eqo = self::getEqo($data->qnbr);
		$quote = $eqo->getEditableQuote();

		$html  = '';
		$html .= self::displayHeader($data);
		$html .= self::headerForm($data, $quote);
		$html .= self::items($data, $quote);
		$html .= self::addItemForm($data);
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

	private static function headerForm($data, EditableQuote $quote) {
		$eqo = self::getEqo($data->qnbr);
		$customer = CustomerQuery::create()->findOneByCustid($quote->custid);
		$html = '';

		if (empty($quote->errormsg) === false) {
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $quote->errormsg]);
		}
		$html .= self::pw('config')->twig->render('quotes/quote/edit/header/form.twig', ['quote' => $quote, 'states' => $eqo->getStates(), 'shipvias' => $eqo->getShipvias(), 'warehouses' => $eqo->getWarehouses(), 'shiptos' => $customer->get_shiptos()]);
		return $html;
	}

	private static function items($data, EditableQuote $quote) {
		$eqo = self::getEqo($data->qnbr);
		$htmlWriter = self::pw('modules')->get('HtmlWriter');

		$html = '';
		$html .= self::pw('config')->twig->render('quotes/quote/edit/items.twig', ['quote' => $quote, 'eqo' => $eqo]);
		return $html;
	}

	private static function addItemForm($data) {
		$htmlWriter = self::pw('modules')->get('HtmlWriter');
		$twig = self::pw('config')->twig;

		self::pw('page')->js .= $twig->render('quotes/quote/edit/lookup/js.twig');

		$html = '';
		$html .= $twig->render('quotes/quote/edit/lookup/form.twig');

		if ($data->q) {
			$custID = QuoteQuery::create()->select(QtModel::aliasproperty('custid'))->findOneByQuoteid($data->qnbr);
			$pricing = self::pw('modules')->get('ItemPricing');
			$pricing->request_search($data->q, $custID);
			$results = $pricing->getAll();
			$html .= $twig->render('quotes/quote/edit/lookup/results.twig', ['q' => $data->q, 'results' => $results]);
		}

		return $html;
	}

/* =============================================================
	URL functions
============================================================= */
	public static function quoteUnlockUrl($qnbr) {
		return self::quoteUrl($qnbr);
	}

	public static function removeItemUrl($qnbr, int $linenbr = 0) {
		$url = new Purl(self::quoteEditUrl($qnbr));
		$url->query->set('action', 'delete-item');
		$url->query->set('linenbr', $linenbr);
		return $url->getUrl();
	}

/* =============================================================
	Supplemental
============================================================= */
	protected static function getEqo($qnbr = '') {
		$eqo = self::pw('modules')->get('Eqo');
		if ($qnbr) {
			$eqo->setQnbr($qnbr);
		}
		return $eqo;
	}


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
