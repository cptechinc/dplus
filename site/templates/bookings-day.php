<?php
	$modules->get('BookingsPages')->init_bookingspage();
	$filter_bookings = $modules->get('FilterBookings');
	$filter_bookings->set_user($user);


	if ($input->get->date) {
		$page->date = $date = date('m/d/Y', strtotime($input->get->text('date')));

		if ($input->get->ordn) {
			$ordn = $input->get->text('ordn');
			$page->title = "$date Bookings for Order # $ordn";
			$lines = $filter_bookings->bookings_day->get_day_salesorder_changes($input)->find();
			$page->body .= $config->twig->render("bookings/day/sales-order-changes.twig", ['page' => $page, 'lines' => $lines]);
		} else {
			$orders = $filter_bookings->bookings_day->get_bookings($input)->find();
			$page->title = "$date Bookings";
			$page->body .= $config->twig->render("bookings/day/sales-order-list.twig", ['page' => $page, 'orders' => $orders]);
		}
	} else {
		$page->title = "Pick a Date to view bookings";
		$page->body .= $config->twig->render("bookings/day/day-form.twig", ['page' => $page]);
	}


	include __DIR__ . "/basic-page.php";
