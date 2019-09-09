<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$query = QuoteQuery::create();

	if ($user->is_salesrep()) {
		$query->filterbySalesPerson($user->roleid);
	}

	$query->orderByDate_quoted('DESC');

	if ($input->get->filter) {
		// TODO: Filter
	} else {
		$input->get->status = array();
	}

	$quotes = $query->paginate($input->pageNum, 10);

	// TODO: Filter Form
	//$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('quotes/quotes-list.twig', ['quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);
	include __DIR__ . "/basic-page.php";
