<?php
	$rm = strtolower($input->requestMethod());
	$values = $values;
	$html = $modules->get('HtmlWriter');
	$upcx = $modules->get('XrefUpc');
	$filter = $modules->get('FilterXrefItemUpc');

	if ($values->action) {
		$upcx->process_input($input);
		$code = $values->text('upc');
		$itemID = $values->text('itemID');

		if ($code) {
			$session->redirect($page->upcURL($code), $http301 = false);
		} else {
			$session->redirect($page->upcURL(), $http301 = false);
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
		$page->headline = "UPCX: UPC $code";
		$upc = $upcx->get_create_xref($code);

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

		if ($upc->isNew()) {
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
			if ($upcx->recordlocker->function_locked($code) && !$upcx->recordlocker->function_locked_by_user($code)) {
				$msg = "UPC $code is being locked by " . $upcx->recordlocker->get_locked_user($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$upcx->recordlocker->function_locked($code)) {
				$upcx->recordlocker->create_lock($code);
			}
		}

		$page->body .= $config->twig->render('items/upcx/form.twig', ['page' => $page, 'upcx' => $upcx, 'upc' => $upc, 'recordlocker' => $upcx->recordlocker]);
		$page->js   .= $config->twig->render('items/upcx/js.twig', ['page' => $page, 'upc' => $upc]);
	} else {
		$upcx->recordlocker->remove_lock($page->name);
		$filter = $modules->get('FilterXrefItemUpc');
		$q = strtoupper($values->text('q'));

		if ($values->q) {
			$page->headline = "UPCX: Searching for '$q'";
			$filter->search($q);
		}

		$filter->apply_sortby($page);
		$upcs = $filter->query->paginate($input->pageNum, 10);
		$page->body .= $config->twig->render('items/upcx/search.twig', ['page' => $page, 'q' => $q]);
		$page->body .= $config->twig->render('items/upcx/upc-list.twig', ['page' => $page, 'upcx' => $upcx, 'upcs' => $upcs]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $upcs->getNbResults()]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
