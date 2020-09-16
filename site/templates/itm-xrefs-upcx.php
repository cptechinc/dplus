<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$itm = $modules->get('Itm');
	$itm_xrefs = $modules->get('ItmXrefs');
	$recordlocker = $modules->get('RecordLockerUser');
	$upcx = $modules->get('XrefUpc');
	$filter_upcs = $modules->get('FilterXrefItemUpc');
	$html = $modules->get('HtmlWriter');

	if ($values->action) {
		$upcx->process_input($input);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$code = $values->text('action') == 'remove-upcx' ? '' : $values->text('upc');
		$session->redirect($page->upcURL($code));
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/itm/bread-crumbs.twig', ['page' => $page, 'page_itm' => $page->parent, 'input' => $input]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->itemID) {
		$itemID = strtoupper($input->get->text('itemID'));

		if ($itm->item_exists($itemID)) {
			$item = $itm->get_item($itemID);
			$unitsofm = UnitofMeasurePurchaseQuery::create()->find();

			if ($input->get->upc) {
				$code = $input->get->text('upc');

				if ($upcx->upc_exists($code)) {
					$upc = $upcx->get_upc($code);
					$page->title = "ITM: $itemID UPC $code";
				} else {
					$upc = new ItemXrefUpc();

					if ($input->get->itemID) {
						if ($upcx->validate_itemID($itemID)) {
							$page->title = "Adding UPC X-ref for $itemID";
							$upc->setItemid($itemID);
						} else {
							$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID $itemID not found in the Item Master"]);
							$page->body .= $html->div('class=mb-3');
						}
					}
				}

				if (!$upc->isNew()) {
					/**
					 * Show alert that UPC is locked if
					 * NOTE $page->lockcode is defined in Itm.module
					 *  1. UPC isn't new
					 *  2. The UPC has a record lock
					 *  3. Userid does not match the lock
					 * Otherwise if not locked, create lock
					 */
					if ($recordlocker->function_locked($page->lockcode, $code) && !$recordlocker->function_locked_by_user($page->lockcode, $code)) {
						$msg = "UPC $code is being locked by " . $recordlocker->get_locked_user($page->lockcode, $code);
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "UPC $code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
						$page->body .= $html->div('class=mb-3');
					} elseif (!$recordlocker->function_locked($page->lockcode, $code)) {
						$recordlocker->create_lock($page->lockcode, $code);
					}
				}

				$page->body .= $config->twig->render('items/itm/xrefs/upcx/form.twig', ['page' => $page, 'upc' => $upc, 'unitsofm' => $unitsofm, 'recordlocker' => $recordlocker]);
				$url_validate = $pages->get('pw_template=upcx-validate')->httpUrl;
				$page->js .= $config->twig->render('items/upcx/js.twig', ['upc' => $upc, 'url_validate' => $url_validate]);
				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			} else {
				$recordlocker->remove_lock($page->lockcode);
				$filter_upcs->filter_query($input);
				$filter_upcs->apply_sortby($page);
				$upcs = $filter_upcs->query->paginate($input->pageNum, 10);

				$page->title = "ITM: UPCs for $itemID";
				$page->body .= $config->twig->render('items/itm/xrefs/upcx/list.twig', ['page' => $page, 'upcs' => $upcs, 'itemID' => $itemID, 'recordlocker' => $recordlocker]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $upcs->getNbResults()]);
			}
		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
