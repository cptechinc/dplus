<?php
	$rm = strtolower($input->requestMethod());
	$values = $values;
	$html = $modules->get('HtmlWriter');
	$upcx = $modules->get('XrefUpc');
	$filter = $modules->get('FilterXrefItemUpc');
	$recordlocker = $modules->get('RecordLockerUser');


	if ($values->action) {
		$upcx->process_input($input);
		$code = $values->text('upc');
		$itemID = $values->text('itemID');

		if ($code) {
			$session->redirect($page->upcURL($code));
		} else {
			$session->redirect($page->upcURL($code));
		}
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/upcx/bread-crumbs.twig', ['page' => $page, 'input' => $input]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->upc) {
		$code = $input->get->text('upc');

		if ($upcx->upc_exists($code)) {
			$upc = $upcx->get_upc($code);
			$page->headline = "UPCX: UPC $code";
		} else {
			$upc = new ItemXrefUpc();
			$upc->setQty(1);

			if ($input->get->itemID) {
				$itemID = $input->get->text('itemID');

				if ($upcx->validate_itemID($itemID)) {
					$page->headline = "Adding UPC X-ref for $itemID";
					$upc->setItemid($itemID);
				} else {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID $itemID not found in the Item Master"]);
					$page->body .= $html->div('class=mb-3');
				}
			}

			if ($code == 'new') {
				$page->headline = "Adding UPC X-ref";
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "UPC $code not found, you may create it below"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$upc->isNew()) {
			/**
			 * Show alert that UPC is locked if
			 *  1. UPC isn't new
			 *  2. The UPC has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->name, $code) && !$recordlocker->function_locked_by_user($page->name, $code)) {
				$msg = "UPC $code is being locked by " . $recordlocker->get_locked_user($page->name, $code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->name, $code)) {
				$recordlocker->create_lock($page->name, $code);
			}
		}

		$page->body .= $config->twig->render('items/upcx/form.twig', ['page' => $page, 'upcx' => $upcx, 'upc' => $upc, 'recordlocker' => $recordlocker]);
		$page->js   .= $config->twig->render('items/upcx/js.twig', ['page' => $page, 'upc' => $upc]);
	} else {
		$filter = $modules->get('FilterXrefItemUpc');
		$recordlocker->remove_lock($page->name);
		$itemID = strtoupper($input->get->text('itemID'));
		$filter->filter_query($input);
		$filter->apply_sortby($page);
		$upcs = $filter->query->paginate($input->pageNum, 10);

		if ($input->get->itemID) {
			if ($upcx->validate_itemID($itemID)) {
				$page->headline = "UPCs for $itemID";
			}
		}

		$page->body .= $config->twig->render('items/upcx/upc-filters.twig', ['page' => $page, 'input' => $input]);
		$page->body .= $config->twig->render('items/upcx/upc-list.twig', ['page' => $page, 'upcs' => $upcs, 'itemID' => $itemID, 'recordlocker' => $recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $upcs->getNbResults()]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
