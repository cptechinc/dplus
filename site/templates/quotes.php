<?php
	$filter_quotes = $modules->get('FilterQuotes');
	$filter_quotes->init_query($user);
	$filter_quotes->filter_query($input);

	$query = $filter_quotes->get_query();
	
	if ($page->has_orderby()) {
		$orderbycolumn = $page->orderby_column;
		$sort = $page->orderby_sort;
		$tablecolumn = Quote::get_aliasproperty($orderbycolumn);
		$query->sortBy($tablecolumn, $sort);
	} else {
		$query->orderByDate_quoted('DESC');
	}

	$quotes = $query->paginate($input->pageNum, 10);

	// TODO: Filter Form
	//$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('quotes/quotes-list.twig', ['page' => $page, 'quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);
	include __DIR__ . "/basic-page.php";
