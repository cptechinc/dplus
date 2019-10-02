<?php
	$query = CustindexQuery::create();

	if ($input->get->q) {
		$q = strtoupper($input->get->text('q'));

		$page->title = "Phonebook: Searching for '$q'";
		// TODO: Add Filtering Function
	}

	if ($page->has_orderby()) {
		$orderbycolumn = $page->orderby_column;
		$sort = $page->orderby_sort;
		$query->sortBy($orderbycolumn, $sort);
	}

	if ($user->is_salesrep()) { // TODO ADD filtering functions to the CustIndexQuery to filter to available customer shiptos
		//$query->filterByCustid($user->get_customers(), Criteria::IN);
	}

	$contacts = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;

	//TODO Create twig file to show results like the old portal but without last sales date
	//$page->body .= $config->twig->render('customers/customer-search.twig', ['page' => $page, 'contacts' => $contacts]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);

	include __DIR__ . "/basic-page.php";
