<?php
	include_once('./itm-prepend.php');
	$upcx = $modules->get('XrefUpc');
	$validate = $modules->get('ValidateUpcx');
	$html = $modules->get('HtmlWriter');

	if ($values->action) {
		$upcx->process_input($input);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$code = $values->text('action') == 'delete-upcx' ? '' : $values->text('upc');
		$session->redirect($page->upcURL($code). $http301 = false);
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	$item = $itm->get_item($itemID);

	if ($input->get->upc) {
		$code = $input->get->text('upc');

		if ($upcx->xref_itemid_matches($code, $itemID) === false) {
			$session->redirect($page->upcURL(). $http301 = false);
		}

		$upc = $upcx->get_create_xref($code);
		$page->title = "ITM: $itemID UPC $code";

		if (!$upc->isNew()) {
			/**
			 * Show alert that UPC is locked if
			 * NOTE $page->lockcode is defined in Itm.module
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

		$page->body .= $config->twig->render('items/itm/xrefs/upcx/form.twig', ['page' => $page, 'upcx' => $upcx, 'upc' => $upc]);
		$page->js .= $config->twig->render('items/upcx/js.twig', ['page' => $page, 'upc' => $upc]);
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	} else {
		$filter = $modules->get('FilterXrefItemUpc');
		$upcx->recordlocker->remove_lock($page->lockcode);
		$filter->filter_query($input);
		$filter->apply_sortby($page);
		$upcs = $filter->query->paginate($input->pageNum, 10);

		$page->title = "ITM: UPCs for $itemID";
		$page->body .= $config->twig->render('items/itm/xrefs/upcx/list.twig', ['page' => $page, 'upcs' => $upcs, 'itemID' => $itemID, 'upcx' => $upcx]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $upcs->getNbResults()]);
	}
	include __DIR__ . "/basic-page.php";
