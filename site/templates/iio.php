<?php
	$iio = $modules->get('Iio');
	$iio->init();
	$page->title = "Item Information Options";

	if ($values->action) {
		$iio->process_input($input);
		$session->sql = $db_dplusdata->getLastExecutedQuery();
		$session->redirect($page->iioURL($values->text('userID')), $http301 = false);
	}

	if ($iio->has_response()) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $iio->response()]);
	}

	$iiuser = $iio->get_create($values->text('userID'));

	if (!$iiuser->isNew()) {
		/**
		 * Show alert that CXM is locked if
		 *  1. CXM isn't new
		 *  2. The CXM has a record lock
		 *  3. Userid does not match the lock
		 * Otherwise if not locked, create lock
		 */
		 if (!$iio->lockrecord($iiuser->userid)) {
			$msg = "IIO $iiuser->userid is being locked by " . $iio->recordlocker->get_locked_user($iiuser->userid);
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "IIO $iiuser->userid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$page->body .= $html->div('class=mb-3');
		}
	} else {
		$iio->recordlocker->remove_lock();
	}

	$page->body .= $config->twig->render('mii/iio/page.twig', ['page' => $page, 'iio' => $iio, 'user' => $iiuser]);
	$page->js   .= $config->twig->render('mii/iio/js.twig', ['page' => $page, 'iio' => $iio]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	$session->remove('response_iio');
	include __DIR__ . "/basic-page.php";
