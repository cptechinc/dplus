<?php
	$iio = $modules->get('Iio');
	$page->title = "Item Information Options";

	if ($values->action) {
		$iio->process_input($input);
		// $session->redirect($page->iioURL($values->text('loginID')), $http301 = false);
	}

	if ($iio->has_response()) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $iio->response()]);
	}

	$iiuser = $iio->get_create($values->text('userID'));

	$page->body .= $config->twig->render('mii/iio/page.twig', ['page' => $page, 'iio' => $iio, 'user' => $iiuser]);
	// $page->js   .= $config->twig->render('mii/iio/js.twig', ['page' => $page, 'iio' => $iio]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
