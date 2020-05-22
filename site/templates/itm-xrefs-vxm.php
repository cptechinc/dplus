<?php
	$itm = $modules->get('Itm');
	$recordlocker = $modules->get('RecordLockerUser');
	$vxm = $modules->get('XrefVxm');
	$filter_vxm = $modules->get('FilterXrefItemVxm');
	$html = $modules->get('HtmlWriter');
	$page->title = "VXM";

	if ($input->requestMethod('POST') || $input->get->action) {
		$vxm->process_input($input);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$vendoritemID = $values->text('action') == 'remove-vxm-item' ? '' : $input->$rm->text('vendoritemID');

		// TODO: Redirect
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

			if ($input->get->vendorID) {
				$vendorID = $input->get->text('vendorID');

				if ($input->get->vendoritemID) {
					$vendoritemID = $input->get->text('vendoritemID');

					if ($vxm->vendors_item_exists($vendorID, $vendoritemID)) {
						$vendor = $modules->get('ViLoadVendorShipfrom')->set_vendorID($vendorID)->get_vendor();
						$item = $vxm->get_vxm_item($vendorID, $vendoritemID);
					} else {
						// TODO
					}

					if (!$item->isNew()) {
						/**
						 * Show alert that VXM is locked if
						 *  1. VXM isn't new
						 *  2. The VXM has a record lock
						 *  3. Userid does not match the lock
						 * Otherwise if not locked, create lock
						 */
						if ($recordlocker->function_locked($page->name, $vxm->get_recordlocker_key($item)) && !$recordlocker->function_locked_by_user($page->name, $vxm->get_recordlocker_key($item))) {
							$msg = "VXM ". $vxm->get_recordlocker_key($item) ." is being locked by " . $recordlocker->get_locked_user($page->name, $vxm->get_recordlocker_key($item));
							$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM $vxm->get_recordlocker_key($item) is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
							$page->body .= $html->div('class=mb-3');
						} elseif (!$recordlocker->function_locked($page->name, $vxm->get_recordlocker_key($item))) {
							$recordlocker->create_lock($page->name, $vxm->get_recordlocker_key($item));
						}
					}

				$unitsofm = UnitofMeasurePurchaseQuery::create()->find();
				$page->headline = "ITM: $itemID VXM Item $vendoritemID for $vendorID";
				$page->body .= $config->twig->render('items/vxm/vendor/item.twig', ['page' => $page, 'item' => $item, 'vendor' => $vendor, 'unitsofm' => $unitsofm, 'vxm' => $vxm, 'recordlocker' => $recordlocker]);
				$page->js .= $config->twig->render('items/vxm/vendor/item/js.twig', ['item' => $item, 'url_validate' => $pages->get('pw_template=vxm-validate')->httpUrl]);
				}
			} else {
				$filter_vxm->filter_query($input);
				$filter_vxm->apply_sortby($page);
				$page->headline = "ITEM: VXM Item $itemID";
				$items = $filter_vxm->query->paginate($input->pageNum, 10);

				$page->body .= $html->h3('', $items->getNbResults() ." VXM Items for $itemID");
				$page->body .= $config->twig->render('items/vxm/item/item-list.twig', ['page' => $page, 'items' => $items, 'vxm' => $vxm, 'recordlocker' => $recordlocker]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			}

		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
