<?php
$cart = $modules->get('Cart');
$html = $modules->get('HtmlWriter');

$rm = strtolower($input->requestMethod());
$values = $input->$rm;

if ($values->action) {
	$cart->process_input($input);
	$session->redirect($page->redirectURL(), $http301 = false);
}

if ($cart->has_custid()) {
	$custID = $cart->get_custid();
	$customer = CustomerQuery::create()->findOneByCustid($custID);
	$shipto = CustomerShiptoQuery::create()->filterByCustid($custID)->filterByShiptoid($cart->get_shiptoid())->findOne();

	$page->title = "Cart for $customer->name";

	if ($shipto) {
		$page->title = "Cart for $shipto->name";
	}

	$page->body .= $config->twig->render('cart/cart-links.twig', ['page' => $page, 'customer' => $customer, 'shipto' => $shipto, 'cart' => $cart]);

	if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
		$lastsold = $modules->get('LastSoldItemsCustomerCstk');
		$lastsold->custID = $cart->get_custid();
		$lastsold->shiptoID = $cart->get_shiptoid();
		$lastsold->function = 'cart';
		$lastsold->request_pricing();
	} else {
		$lastsold = false;
	}

	if ($config->twigloader->exists("cart/$config->company/cart-items.twig")) {
		$page->body .= $config->twig->render("cart/$config->company/cart-items.twig", ['page' => $page, 'cart' => $cart]);
	} else {
		$page->body .= $config->twig->render('cart/cart-items.twig', ['page' => $page, 'cart' => $cart]);
	}
	$page->js .= $config->twig->render('cart/js.twig', ['page' => $page, 'cart' => $cart]);

	if ($config->twigloader->exists("cart/lookup/$config->company/form.twig")) {
		$page->body .= $config->twig->render("cart/lookup/$config->company/form.twig", ['page' => $page, 'cart' => $cart]);
		$page->js   .= $config->twig->render("cart/lookup/$config->company/js.twig", ['page' => $page, 'cart' => $cart]);
	} else {
		$page->body .= $config->twig->render('cart/lookup/form.twig', ['page' => $page, 'cart' => $cart]);
		$page->js   .= $config->twig->render('cart/lookup/js.twig', ['page' => $page, 'cart' => $cart]);
	}

	$page->body .= $config->twig->render('cart/last-sales/modal.twig', ['page' => $page, 'cart' => $cart, 'lastsold' => $lastsold, 'company' => $config->company, 'loader' => $config->twig->getLoader()]);

	if ($input->get->q) {
		$q = $input->get->text('q');
		$cart->request_itemsearch($q);
		$results = PricingQuery::create()->findBySessionid(session_id());
		$page->body .= $config->twig->render('cart/lookup/results.twig', ['page' => $page, 'cart' => $cart, 'q' => $q, 'results' => $results]);
	}

	$page->body .= $html->div('class=mb-4', '');
	$page->body .= $config->twig->render('cart/cart-actions.twig', ['page' => $page, 'cart' => $cart, 'user' => $user]);
} elseif ($input->get->custID) {
	$custID = $input->get->text('custID');
	$cart->set_custid($custID);
	if ($input->get->shiptoID) {
		$shiptoID = $input->get->text('shiptoID');
		$cart->set_shiptoID($shiptoID);
	}
	$session->redirect($page->url);
} else {
	$page->body .= $config->twig->render('cart/form/customer-form.twig', ['page' => $page]);
	$page->js   .= $config->twig->render('cart/form/js.twig', ['page' => $page]);
}

$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

include __DIR__ . "/basic-page.php";
