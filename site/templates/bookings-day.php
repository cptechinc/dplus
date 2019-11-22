<?php
	$modules->get('DpagesBookings')->init_bookingspage();
	$filter_bookings = $modules->get('FilterBookings');
	$filter_bookings->set_user($user);
	$page->show_breadcrumbs = false;

	if ($input->get->date) {
		$page->date = $date = date('m/d/Y', strtotime($input->get->text('date')));

		if ($input->get->custID) {
			$custID = $input->get->text('custID');
			$load_customer = $modules->get('CiLoadCustomerShipto');
			$load_customer->set_custID($custID);
			$page->customer = $customer = $load_customer->get_customer();

			if ($input->get->shiptoID) {
				$shiptoID = $input->get->text('shiptoID');
				$load_customer->set_shiptoID($shiptoID);
				$page->shipto = $shipto = $load_customer->get_shipto();
			}
		}

		if ($input->get->ordn) {
			$ordn = $input->get->text('ordn');
			$page->ordn = $ordn;
			$page->title = "Bookings for Order # $ordn on $date";
			$lines = $filter_bookings->bookings_day->get_day_salesorder_changes($input)->find();
			$page->body .= $config->twig->render("bookings/bread-crumbs.twig", ['page' => $page]);
			$page->body .= $config->twig->render("bookings/day/sales-order-changes.twig", ['page' => $page, 'lines' => $lines]);
		} else {
			$orders = $filter_bookings->bookings_day->get_bookings($input)->find();
			$page->title = "Bookings on $date";

			if ($input->get->custID) {
				$page->title = "$customer->name ($customer->custid) $date Bookings";

				if ($input->get->shiptoID) {
					$page->title = "$shipto->name ($shipto->shiptoid) $date Bookings";
				}
			}
			$page->body .= $config->twig->render("bookings/bread-crumbs.twig", ['page' => $page]);
			$page->body .= $config->twig->render("bookings/day/sales-order-list.twig", ['page' => $page, 'orders' => $orders]);
		}
	} else {
		$page->title = "Pick a Date to view bookings";
		$page->body .= $config->twig->render("bookings/day/day-form.twig", ['page' => $page]);
	}


	include __DIR__ . "/basic-page.php";
