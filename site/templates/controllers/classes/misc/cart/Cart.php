<?php namespace Controllers\Misc\Cart;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus CRUD
use Dplus\Cart\Cart as Manager;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mci\Ci\Ci;
use Controllers\Mqo\Quote\Quote;
use Controllers\Mso\SalesOrder\SalesOrder;

class Cart extends AbstractController {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'custID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		$cart = self::getCart();

		if (empty($data->custID) === false) {
			$cart->setCustid($data->custID);
		}

		if ($cart->hasCustid() === false) {
			return self::selectCustomer($data);
		}
		return self::cart($data);
	}

	public static function handleCRUD($data) {
		$fields = ['action|text', 'custID|text'];
		self::sanitizeParametersShort($data, $fields);
		$cart = self::getCart();
		$cart->processInput(self::pw('input'));
		$url = self::cartUrl();

		switch ($data->action) {
			case 'create-quote':
				$url = Quote::quoteEditNewUrl();
				break;
			case 'create-order':
				$url = SalesOrder::orderEditNewUrl();
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	private static function selectCustomer($data) {
		$fields = ['q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->headline = "Select a Customer";
		self::pw('page')->js .= self::pw('config')->twig->render('cart/form/js.twig');
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayCustomerForm($data);
	}

	private static function cart($data) {
		$cart     = self::getCart();
		$page     = self::pw('page');
		$config   = self::pw('config');
		$modules  = self::pw('modules');
		$customer = $cart->getCustomer($cart->getCustid());
		$page->headline = "Cart for $customer->name";

		if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
			$lastsold = $modules->get('LastSoldItemsCustomerCstk');
			$lastsold->custID   = $cart->getCustid();
			$lastsold->shiptoID = $cart->getShiptoid();
			$lastsold->function = 'cart';
			$lastsold->request_pricing();
		} else {
			$lastsold = false;
		}
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->js .= self::pw('config')->twig->render('cart/js.twig', ['cart' => $cart]);

		if ($config->twigloader->exists("cart/lookup/$config->company/form.twig")) {
			$page->js .= $config->twig->render("cart/lookup/$config->company/js.twig", ['cart' => $cart]);
		} else {
			$page->js .= $config->twig->render('cart/lookup/js.twig', ['cart' => $cart]);
		}
		return self::displayCart($data);
	}

/* =============================================================
	Urls
============================================================= */
	public static function cartUrl() {
		return self::pw('pages')->get('pw_template=cart')->url;
	}

	public static function setCustomerUrl($custID, $shiptoID = '') {
		$url = new Purl(self::cartUrl());
		$url->query->set('custID', $custID);
		if ($shiptoID) {
			$url->query->set('shiptoID', $shiptoID);
		}
		return $url->getUrl();
	}

	/**
	 * Return Url to delete Line Item
	 * @param  int    $linenbr Line Number
	 * @return string
	 */
	public static function deleteItemUrl($linenbr = 0) {
		$url = new Purl(self::cartUrl());
		$url->query->set('action', 'delete-item');
		$url->query->set('linenbr', $linenbr);
		return $url->getUrl();
	}

	/**
	 * Return Url to Empty Cart
	 * @return string
	 */
	public static function emptyCartUrl() {
		$url = new Purl(self::cartUrl());
		$url->query->set('action', 'empty-cart');
		return $url->getUrl();
	}

	/**
	 * Return Url to Create Order
	 * @return string
	 */
	public static function createOrderUrl() {
		$url = new Purl(self::cartUrl());
		$url->query->set('action', 'create-order');
		return $url->getUrl();
	}

	/**
	 * Return Url to Create Quote
	 * @return string
	 */
	public static function createQuoteUrl() {
		$url = new Purl(self::cartUrl());
		$url->query->set('action', 'create-quote');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayCustomerForm($data) {
		return self::pw('config')->twig->render('cart/form/customer-form.twig');
	}

	private static function displayCart($data) {
		$html   = '';
		$config = self::pw('config');
		$cart   = self::getCart();
		$customer = $cart->getCustomer();
		$shipto   = $cart->getCustomerShipto();

		$html .= $config->twig->render('cart/cart-links.twig', ['customer' => $customer, 'shipto' => $shipto, 'cart' => $cart]);

		if ($config->twigloader->exists("cart/items/$config->company/list.twig")) {
			$html .= $config->twig->render("cart/items/$config->company/list.twig", ['cart' => $cart]);
		} else {
			$html .= $config->twig->render('cart/items/list.twig', ['cart' => $cart]);
		}

		if ($config->twigloader->exists("cart/lookup/$config->company/form.twig")) {
			$html .= $config->twig->render("cart/lookup/$config->company/form.twig", ['cart' => $cart]);
		} else {
			$html .= $config->twig->render('cart/lookup/form.twig', ['cart' => $cart]);
		}

		$html .= $config->twig->render('cart/actions.twig');
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesCart');

		$m->addHook('Page(pw_template=cart)::deleteItemUrl', function($event) {
			$linenbr = $event->arguments(0);
			$event->return = self::deleteItemUrl($linenbr);
		});

		$m->addHook('Page(pw_template=cart)::emptyCartUrl', function($event) {
			$event->return = self::emptyCartUrl();
		});

		$m->addHook('Page(pw_template=cart)::ciUrl', function($event) {
			$page = $event->object;
			$custID = $event->arguments(0);
			$event->return = Ci::ciUrl($custID);
		});

		$m->addHook('Page(pw_template=cart)::ciShiptoUrl', function($event) {
			$page = $event->object;
			$custID   = $event->arguments(0);
			$shiptoID = $event->arguments(1);

			$event->return = Ci::ciUrl($custID);

			if ($shiptoID) {
				$event->return = Ci::ciShiptoUrl($custID, $shiptoID);
			}
		});

		$m->addHook('Page(pw_template=cart)::createQuoteUrl', function($event) {
			$event->return = self::createQuoteUrl();
		});

		$m->addHook('Page(pw_template=cart)::createOrderUrl', function($event) {
			$event->return = self::createOrderUrl();
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCart() {
		return Manager::getInstance();
	}
}
