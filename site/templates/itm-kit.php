<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$itm  = $modules->get('Itm');
	$kitm = $modules->get('InvKitMaster');

	if ($input->get->itemID) {
		$kitID = $input->get->text('itemID');

		if ($itm->item_exists($kitID)) {
			if ($values->action) {
				$kitm->process_input($input);
				$kitID = $values->text('itemID');
				$session->redirect($page->itm_miscURL($kitID));
			}

			if ($session->response_itm) {
				$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
			}

			/**
			 * Show alert that Kit is locked if
			 *  1. Kit isn't new
			 *  2. The Kit has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($kitm->recordlocker->function_locked($kitID) && !$kitm->recordlocker->function_locked_by_user($kitID)) {
				$msg = "ITM Item $kitID is being locked by " . $kitm->recordlocker->get_locked_user($kitID);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $kitID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$kitm->recordlocker->function_locked($kitID)) {
				$kitm->recordlocker->create_lock($kitID);
			}

			$page->headline = "Kit for $kitID";
			$item = $itm->get_item($kitID);
			$kit = $kitm->get_kit($kitID);

			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);

			if ($input->get->component) {
				$itemID = $input->get->text('component');
				$component = $itemID == 'new'? new InvKitComponent() : $kitm->get_component($kitID, $itemID);
				$page->headline = $itemID == 'new' ? "ITM Kit - $kitID" : "ITM Kit - $kitID - $itemID";
				$page->body .= $config->twig->render('items/itm/kit/component/page.twig', ['page' => $page, 'kitm' => $kitm, 'kit' => $kit, 'component' => $component]);
				$page->js   .= $config->twig->render('items/itm/kit/component/js.twig', ['page' => $page, 'kitm' => $kitm, 'kit' => $kit, 'component' => $component]);
			} else {
				$page->body .= $config->twig->render('items/itm/kit/page.twig', ['page' => $page, 'kitm' => $kitm, 'kit' => $kit]);
			}

			// $page->js   .= $config->twig->render('items/itm/misc/js.twig', ['page' => $page, 'itm' => $itm]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			if ($session->response_itm) {
				$session->remove('response_itm');
			}
		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
