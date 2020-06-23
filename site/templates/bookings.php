<?php
	$modules->get('DpagesBookings')->init_bookingspage();
	$filter_bookings = $modules->get('FilterBookings');
	$filter_bookings->set_user($user);

	if ($input->get->bookdate) {
		$bookdates = array($input->get->text('bookdate'));

		if (strpos($input->get->text('bookdate'), '|') !== false) {
			$bookdates = explode('|', $input->get->text('bookdate'));
		}

		$input->get->bookdate = $bookdates;

		if (date('F Y', strtotime($bookdates[0])) == date('F Y', strtotime($bookdates[1]))) {
			$page->title = date('F Y', strtotime($bookdates[0])) . ' Bookings';
		} else {
			$page->title = "Bookings between " . $bookdates[0] . ' and ' . $bookdates[1];
		}
	} else {
		$page->title = date('Y'). " Bookings";
	}

	$bookings = $filter_bookings->bookings_user->get_bookings($input)->find();
	$customers = $filter_bookings->bookings_customer->get_bookings_by_customer($input)->find();

	$page->body .= $config->twig->render("bookings/user/bookings-page.twig", ['page' => $page, 'bookings' => $bookings, 'interval' => $filter_bookings->bookings_user->interval, 'customers' => $customers]);
	$page->js .= $config->twig->render("bookings/user/js/bookings.js.twig", ['page' => $page, 'bookingsdata' => $filter_bookings->convert_bookings_for_js($bookings), 'interval' => $filter_bookings->bookings_user->interval]);

	$config->styles->append(hash_templatefile('styles/lib/morris.css'));
	$config->scripts->append(hash_templatefile('scripts/lib/raphael.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/morris.js'));
	include __DIR__ . "/basic-page.php";
