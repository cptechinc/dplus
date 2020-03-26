<?php
	$cart = $modules->get('Cart');
	$html = $modules->get('HtmlWriter');

	if ($cart->has_custid()) {
		$custID = $cart->get_custid();
		$customer = CustomerQuery::create()->findOneByCustid($custID);
		$page->title = "Cart for $customer->name";

		if ($cart->has_shiptoid()) {
			$shiptoID = $cart->get_shiptoID();
			$shipto = CustomerShiptoQuery::create()->filterByCustid($custID)->findOneByShiptoid($shiptoID);
			$page->title = "Cart for $shipto->name";
		} else {
			$shiptoID = '';
			$shipto = false;
		}

		$page->formurl = $page->child('template=redir')->url;
		$page->body .= $config->twig->render('cart/cart-links.twig', ['page' => $page, 'customer' => $customer, 'cart' => $cart, 'shipto' => $shipto]);


		if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
			$lastsold = $modules->get('LastSoldItemsCustomerCstk');
			$lastsold->custID = $custID;
			$lastsold->shiptoID = $shiptoID;
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

		if ($config->twigloader->exists("cart/$config->company/add-item-form.twig")) {
			$page->body .= $config->twig->render("cart/$config->company/add-item-form.twig", ['page' => $page, 'cart' => $cart]);
		} else {
			$page->body .= $config->twig->render('cart/add-item-form.twig', ['page' => $page, 'cart' => $cart]);
		}

		$page->js .= $config->twig->render('cart/item-lookup.js.twig', ['page' => $page, 'cart' => $cart]);
		$page->body .= $config->twig->render('cart/last-sales/modal.twig', ['page' => $page, 'cart' => $cart, 'lastsold' => $lastsold, 'company' => $config->company, 'loader' => $config->twig->getLoader()]);

		if ($input->get->q) {
			$q = $input->get->text('q');
			$cart->request_itemsearch($q);
			$results = PricingQuery::create()->findBySessionid(session_id());
			$page->body .= $config->twig->render('cart/lookup-results.twig', ['page' => $page, 'cart' => $cart, 'q' => $q, 'results' => $results]);
		}

		$page->body .= $html->div('class=mb-4', '');
		$page->body .= $config->twig->render('cart/cart-actions.twig', ['page' => $page, 'cart' => $cart, 'user' => $user]);

		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	} elseif ($input->get->custID) {
		$custID = $input->get->text('custID');
		$cart->set_custid($custID);
		if ($input->get->shiptoID) {
			$shiptoID = $input->get->text('shiptoID');
			$cart->set_shiptoID($shiptoID);
		}
		$session->redirect($page->url);
	} else {
		$query = CustomerQuery::create();

		if ($input->get->q) {
			$q = $input->get->text('q');
			$page->title = "CI: Searching for '$q'";
			$col_custid = Customer::get_aliasproperty('custid');
			$col_name = Customer::get_aliasproperty('name');
			$columns = array($col_custid, $col_name);
			$query->search_filter($columns, strtoupper($q));
		}

		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$tablecolumn = Customer::get_aliasproperty($orderbycolumn);
			$query->sortBy($tablecolumn, $sort);
		}

		$customers = $query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	}

include __DIR__ . "/basic-page.php";
