<?php
	$filter_quotes = $modules->get('FilterQuotes');
	$filter_quotes->init_query($user);
	$filter_quotes->filter_query($input);
	$filter_quotes->apply_sortby($page);
	$query = $filter_quotes->get_query();

	$quotes = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('quotes/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('quotes/quotes-list-links.twig', ['page' => $page, 'quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);
	include __DIR__ . "/basic-page.php";
