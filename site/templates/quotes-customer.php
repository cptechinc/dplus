<?php
	$filter_quotes = $modules->get('FilterQuotes');
	$filter_quotes->init_query($user);
	$filter_quotes->filter_query($input);
	$filter_quotes->apply_sortby($page);
	$query = $filter_quotes->get_query();

	$load_customer = $modules->get('CiLoadCustomerShipto');
	$load_customer->set_custID($input->get->text('custID'));
	$customer = $load_customer->get_customer();
	$page->title = "$customer->name Quotes";

	if ($input->get->shiptoID) {
		$load_customer->set_shiptoID($input->get->text('shiptoID'));
		$shipto = $load_customer->get_shipto();
		$page->title = "$shipto->name Quotes";
	}

	$quotes = $query->paginate($input->pageNum, 10);
	$page->body = $config->twig->render('quotes/customer/search-form.twig', ['page' => $page, 'input' => $input, 'custID' => $load_customer->get_custID(), 'shiptoID' => $load_customer->get_shiptoID()]);
	$page->body .= $config->twig->render('quotes/customer/quotes-list-links.twig', ['page' => $page, 'quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);
	include __DIR__ . "/basic-page.php";
