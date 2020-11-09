<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$itm = $modules->get('ItmMisc');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			if ($values->action) {
				$itm->process_input($input);
				$itemID = $values->text('itemID');
				$session->redirect($page->itm_miscURL($itemID));
			}

			if ($session->response_itm) {
				$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
				$session->remove('response_itm');
			}

			/**
			 * Show alert that warehouse is locked if
			 *  1. Warehouse isn't new
			 *  2. The warehouse has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($itm->recordlocker->function_locked($itemID) && !$itm->recordlocker->function_locked_by_user($itemID)) {
				$msg = "ITM Item $itemID is being locked by " . $itm->recordlocker->get_locked_user($page->lockcode, $itemID);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$itm->recordlocker->function_locked($page->lockcode, $itemID)) {
				$itm->recordlocker->create_lock($page->lockcode, $itemID);
			}

			$page->headline = "Misc for $itemID";
			$item = $itm->get_item($itemID);
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
			$page->body .= $config->twig->render('items/itm/misc/page.twig', ['page' => $page, 'itm' => $itm, 'item' => $item, 'recordlocker' => $itm->recordlocker]);
			$page->js   .= $config->twig->render('items/itm/misc/js.twig', ['page' => $page]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
