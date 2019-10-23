<?php // THIS TEMPLATE IS THE PHONEBOOK FOR ALL CUSTOMERS
	$modules->get('DpagesMci')->init_phonebookpage();

	$query = CustindexQuery::create();

	if ($input->get->q) {
		$q = strtoupper($input->get->text('q'));

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);

		$page->title = "Phonebook: $customer->name Searching for '$q'";
		$query->filterByMatchExpression($q);
	}

	if ($user->is_salesrep()) {
		$query->filterByUserCustperm($user->loginid);
	}

	if ($page->has_orderby()) {
		$orderbycolumn = $page->orderby_column;
		$sort = $page->orderby_sort;
		$query->sortBy($orderbycolumn, $sort);
	}

	$page->searchURL = $page->url;
	$customers = $query->paginate($input->pageNum, 10);
	$page->body .= $config->twig->render('customers/phonebook/phonebook-search.twig', ['page' => $page, 'q' => $q, 'customers' => $customers]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $query->count()]);

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
