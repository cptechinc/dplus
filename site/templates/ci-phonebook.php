<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$query = CustindexQuery::create();
		$query->filterByCustid($custID);

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			$page->title = "Phonebook: $customer->name Searching for '$q'";
			$query->filterByMatchExpression($q);
		}

		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$query->sortBy($orderbycolumn, $sort);
		}

		$contacts = $query->paginate($input->pageNum, 10);
		$page->searchURL = $page->url;
		$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
		$page->body .= $config->twig->render('customers/customer-search.twig', ['page' => $page, 'custID' => $custID, 'contacts' => $contacts]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $query->count()]);
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
