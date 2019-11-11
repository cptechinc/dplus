<?php // THIS TEMPLATE IS THE PHONEBOOK FOR ONE CUSTOMER

	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$query = CustindexQuery::create();
		$query->filterByCustid($custID);

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);

		$page->title = "Phonebook: $customer->name";

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			$page->title = "Phonebook: $customer->name Searching for '$q'";
			$query->filterByMatchExpression($q);
		}

		if ($user->is_salesrep()) {
			$query->filterByShiptoid($user->get_customer_shiptos($custID));
		}

		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$query->sortBy($orderbycolumn, $sort);
		}

		$contacts = $query->paginate($input->pageNum, 10);
		$page->searchURL = $page->url;
		$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
		$page->body .= $config->twig->render('customers/ci/phonebook/contact-search.twig', ['page' => $page, 'custID' => $custID, 'q' => $q, 'contacts' => $contacts]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $query->count()]);
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
