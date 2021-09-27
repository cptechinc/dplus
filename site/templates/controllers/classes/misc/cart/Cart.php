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

class Cart extends AbstractController {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		$cart = self::getCart();

		if ($cart->hasCustid() === false) {
			return self::selectCustomer($data);
		}
		self::cart($data);
	}

	private static function selectCustomer($data) {
		$fields = ['q|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		self::pw('page')->js .= self::pw('config')->twig->render('cart/form/js.twig');
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::displayCustomerForm($data);
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

		// TODO: handle redirect on createX action

		// $m->addHook('Page(pw_template=cart)::redirectUrl', function($event) {
		// 	$p = $event->object;
		// 	$action = $p->fullUrl->query->get('action');
		// 	$url = $this->cartUrl();
		//
		// 	if (strpos($action, 'create') !== false) {
		// 		if ($action == 'create-order' || $action == 'create-blank-order') {
		// 			$purl = new Url($this->wire('pages')->get('pw_template=sales-order-edit')->url);
		// 			$purl->path->add('new');
		// 			$url = $purl->getUrl();
		// 		} elseif($action == 'create-quote') {
		// 			$purl = new Url($this->wire('pages')->get('pw_template=quote-view')->url);
		// 			$purl->path->add('edit');
		// 			$purl->path->add('new');
		// 			$url = $purl->getUrl();
		// 		}
		// 	}
		// 	$event->return = $url;
		// });
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getCart() {
		return Manager::getInstance();
	}
}
