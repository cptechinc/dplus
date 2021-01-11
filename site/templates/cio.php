<?php
	$cio = $modules->get('Cio');
	$cio->init();

	if ($values->action) {
		$cio->process_input($input);
		$session->sql = $db_dplusdata->getLastExecutedQuery();
		$session->redirect($page->cioURL($values->text('userID')), $http301 = false);
	}

	if ($cio->has_response()) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $cio->response()]);
	}

	$iiuser = $cio->get_create($values->text('userID'));

	if (!$iiuser->isNew()) {
		/**
		 * Show alert that CXM is locked if
		 *  1. CXM isn't new
		 *  2. The CXM has a record lock
		 *  3. Userid does not match the lock
		 * Otherwise if not locked, create lock
		 */
		 if (!$cio->lockrecord($iiuser->userid)) {
			$msg = "IIO $iiuser->userid is being locked by " . $cio->recordlocker->get_locked_user($iiuser->userid);
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "IIO $iiuser->userid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$page->body .= $html->div('class=mb-3');
		}
	} else {
		$cio->recordlocker->remove_lock();
	}

	$page->body .= $config->twig->render('mci/cio/page.twig', ['page' => $page, 'cio' => $cio, 'user' => $iiuser]);
	$page->js   .= $config->twig->render('mci/cio/js.twig', ['page' => $page, 'cio' => $cio]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	$session->remove('response_cio');
	include __DIR__ . "/basic-page.php";
