<?php
	if ($input->get->custID) {
		$load_customer = $modules->get('CiLoadCustomerShipto');
		$load_customer->set_custID($input->get->text('custID'));
		$customer = $load_customer->get_customer();
		$page->title = "$customer->name Quotes";

		if ($input->get->shiptoID) {
			$load_customer->set_shiptoID($input->get->text('shiptoID'));
			$shipto = $load_customer->get_shipto();
			$page->title = "$shipto->name Quotes";
		}

		$filter_quotes = $modules->get('FilterQuotes');
		$filter_quotes->init_query($user);
		$filter_quotes->filter_query($input);

		$query = $filter_quotes->get_query();
		$query->orderByDate_quoted('DESC');
		$quotes = $query->paginate($input->pageNum, 10);

		// TODO: Filter Form
		//$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
		$page->body .= $config->twig->render('quotes/customer/quotes-list.twig', ['quotes' => $quotes, 'quotepage' => $pages->get('pw_template=quote-view')->url]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $quotes->getNbResults()]);

	} else {
		$page->title = 'Search for a Customer';
		$query = CustomerQuery::create();
		$exact_query = CustomerQuery::create();

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			if ($exact_query->filterByCustid($q)->count() == 1) {
				$session->redirect($page->url."?custID=$q");
			}

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
