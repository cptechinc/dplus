<?php
	$filter_quotes = $modules->get('FilterQuotes');
	$filter_quotes->init_query($user);
	$filter_quotes->filter_query($input);
	
	$query = $filter_quotes->get_query();
	$query->orderByDate_quoted('DESC');
	$quotes = $query->paginate($input->pageNum, 10);

	// TODO: Filter Form
	//$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('quotes/quotes-list.twig', ['quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);
	include __DIR__ . "/basic-page.php";
