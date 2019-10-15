<?php
	$page->show_breadcrumbs = false;

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		if ($user->has_customer($custID)) {
			$html = $modules->get('HtmlWriter');
			$modules->get('DpagesBookings')->init_bookingspage();
			$filter_bookings = $modules->get('FilterBookings');
			$filter_bookings->set_user($user);

			$load_customer = $modules->get('CiLoadCustomerShipto');
			$load_customer->set_custID($custID);
			$page->customer = $customer = $load_customer->get_customer();
			$page->title = "$customer->name ($customer->custid) ";

			if ($input->get->shiptoID) {
				$shiptoID = $input->get->text('shiptoID');
				$load_customer->set_shiptoID($shiptoID);
				$page->shipto = $shipto = $load_customer->get_shipto();
				$page->title = "$shipto->name ($shipto->shiptoid) ";
			}

			if ($input->get->bookdate) {
				$bookdates = array($input->get->text('bookdate'));

				if (strpos($input->get->text('bookdate'), '|') !== false) {
					$bookdates = explode('|', $input->get->text('bookdate'));
				}

				$input->get->bookdate = $bookdates;

				if (date('F Y', strtotime($bookdates[0])) == date('F Y', strtotime($bookdates[1]))) {
					$page->title .= date('F Y', strtotime($bookdates[0])) . ' Bookings';
				} else {
					$page->title .= "Bookings between " . $bookdates[0] . ' and ' . $bookdates[1];
				}
			} else {
				$page->title .= date('Y'). " Bookings";
			}

			$bookings = $filter_bookings->bookings_user->get_bookings($input)->find();
			$shiptos = $filter_bookings->bookings_customer->get_bookings_by_shipto($input)->find();

			$page->body .= $config->twig->render("bookings/bread-crumbs.twig", ['page' => $page]);

			if ($input->get->shiptoID) {
				$page->body .= $html->h2('', $page->title);
				$page->body .= $config->twig->render("bookings/customer/shipto-links.twig", ['page' => $page, 'customer' => $customer, 'shipto' => $shipto]);
			} else {
				$page->body .= $html->h2('', $page->title);
				$page->body .= $config->twig->render("bookings/customer/links.twig", ['page' => $page, 'customer' => $customer]);
			}

			$page->body .= $config->twig->render("bookings/customer/bookings-page.twig", ['page' => $page, 'bookings' => $bookings, 'interval' => $filter_bookings->bookings_user->interval, 'shiptos' => $shiptos, 'dpluso' => $dpluso]);
			$page->js .= $config->twig->render("bookings/user/js/bookings.js.twig", ['page' => $page, 'bookingsdata' => $filter_bookings->convert_bookings_for_js($bookings), 'interval' => $filter_bookings->bookings_user->interval]);

			$config->styles->append(hash_templatefile('styles/lib/morris.css'));
			$config->scripts->append(hash_templatefile('scripts/lib/raphael.js'));
			$config->scripts->append(hash_templatefile('scripts/lib/morris.js'));
		} else {
			$page->title = "Error! You don't have access to customer $custID";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template can not be found"]);
			$page->searchURL = $page->url;
			$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => []]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> 0]);
		}
	} else {
		$query = CustomerQuery::create();
		$exact_query = CustomerQuery::create();

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			if ($exact_query->filterByCustid($q)->count() == 1) {
				$session->redirect($page->url."?custID=$q");
			}

			$page->title = "Customer Bookings: Searching for '$q'";
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
