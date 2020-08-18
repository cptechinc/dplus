<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$html = $modules->get('HtmlWriter');

	$modules->get('DpagesMwm')->init_picking();
	$pickingsession = $modules->get('Picking');
	$pickingsession->set_sessionID(session_id());
	$pickingsession->set_ordn($ordn);

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');
	$config_picking   = $modules->get('ConfigsWarehousePicking');

	// CHECK If there are details to pick
	$lines_query = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn);
	$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);

	if ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'ordn' => $ordn]);
	} elseif ($lines_query->count() > 0) {
		if ($input->requestMethod('POST')) {
			$pickingsession->handle_action($input);
			$session->redirect($page->fullURL->getUrl());
		}

		if ($session->pickingerror) {
			$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$session->pickingerror"]));
			$session->remove('pickingerror');
		}

		if ($whsesession->has_message()) {
			$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $whsesession->status]));
			$session->remove('pickingerror');
		}

		$page->body .= $config->twig->render('warehouse/picking/order-info.twig', ['page' => $page, 'order' => $order, 'whsesession' => $whsesession]);

		if ($input->get->scan) {
			$scan = $input->get->text('scan');
			$page->scan = $scan;
			$query_phys = $pickingsession->inventory->get_inventory_scan_query($scan, $includepack = false);

			if ($session->verify_whseitempick_items) {
				$query_pickeditems = $pickingsession->get_whseitempick_query(['barcode' => $scan, 'recordnumber' => $session->verify_whseitempick_items]);
				$query_pickeditems->find();

				if ($query_pickeditems->count()) {
					$page->body .= $config->twig->render('warehouse/picking/unguided/scan/verify-whseitempick-lotserials.twig', ['page' => $page, 'm_picking' => $pickingsession, 'scan' => $scan, 'items' => $query_pickeditems->find()]);
				} else {
					$session->remove('verify_whseitempick_items');
					$page->body .= $html->div('class=mb-3');
					$page->body .= $config->twig->render('warehouse/picking/unguided/scan/scan-form.twig', ['page' => $page]);
				}
			} else {
				if ($query_phys->count() == 1) {
					$inventoryitem = $query_phys->findOne();

					if ($inventoryitem->has_error()) {
						$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error searching '$scan'", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $inventoryitem->get_error()]));
						$page->body .= $html->h3('', 'Scan item to add');
						$page->body .= $config->twig->render('warehouse/picking/unguided/scan/scan-form.twig', ['page' => $page]);
					}  else {
						if ($pickingsession->items->is_itemid_onorder($inventoryitem->itemid)) {
							//$item = $modules->get('LoadItem')->get_item($inventoryitem->itemid);
							$orderitem = $pickingsession->items->get_picksalesorderdetail_itemid($inventoryitem->itemid);
							$page->body .= $html->h3('', 'Enter Item Details');
							$page->body .= $config->twig->render('warehouse/picking/unguided/scan/add-scanned-item-form.twig', ['page' => $page, 'm_picking' => $pickingsession, 'item' => $inventoryitem, 'orderitem' => $orderitem, 'scan' => $scan]);
							$page->js   .= $config->twig->render('warehouse/picking/unguided/scan/scan.js.twig', ['page' => $page]);
						} else {
							$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Item Not on Order', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $inventoryitem->itemid is not on this order"]));
							$page->body .= $config->twig->render('warehouse/picking/unguided/scan/scan-form.twig', ['page' => $page]);
						}
					}
				} elseif ($query_phys->count() == 0) {
					$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => '0 items found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No items found for '$scan'"]));
					$page->body .= $config->twig->render('warehouse/picking/unguided/scan/scan-form.twig', ['page' => $page]);
				} else {
					$physicalitems = $query_phys->groupBy('itemid')->find();
					$page->body .= $html->h3('', 'Select Items to Pick');
					$page->body .= $config->twig->render('warehouse/picking/unguided/scan/select-item-list.twig', ['page' => $page, 'items' => $physicalitems, 'm_picking' => $pickingsession]);
					$page->js   .= $config->twig->render('warehouse/picking/unguided/scan/select-item-list.js.twig', []);
				}
			}
			$page->body .= $html->div('class=mb-3');
		} else {
			$page->formurl  = $pages->get('template=redir,redir_file=inventory')->url;
			$page->body .= $html->h3('', 'Scan item to pick');
			$page->body .= $config->twig->render('warehouse/picking/unguided/scan-form.twig', ['page' => $page]);
		}

		if ($pickingsession->items->has_sublines()) {
			$page->body .= $config->twig->render('warehouse/picking/unguided/order-items-sublined.twig', ['page' => $page, 'm_picking' => $pickingsession, 'lineitems' => $lines_query->find()]);
		} else {
			if ($config->twigloader->exists("warehouse/picking/unguided/$config->company/order-items.twig")) {
				$page->body .= $config->twig->render("warehouse/picking/unguided/$config->company/order-items.twig", ['page' => $page, 'm_picking' => $pickingsession, 'lineitems' => $lines_query->find()]);
			} else {
				$page->body .= $config->twig->render('warehouse/picking/unguided/order-items.twig', ['page' => $page, 'm_picking' => $pickingsession, 'lineitems' => $lines_query->find()]);
			}
		}

		$page->body .= $html->div('class=mb-3');
		if (!$input->get->scan) {
			$page->body .= $config->twig->render('warehouse/picking/unguided/order-actions.twig', ['page' => $page]);
		}

		if ($session->removefromline) {
			$page->js .= $config->twig->render('warehouse/picking/unguided/remove-line.js.twig', ['linenbr' => $session->removefromline]);
			$session->remove('removefromline');
		}
	} else { // NO ITEMS TO PICK
		$whsesession->setStatus("There are no detail lines available to pick for Order # $ordn");
		if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
			WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
		}

		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body .= $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		$page->body .= '<div class="form-group"></div>';
		$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}

	if ($session->printpicklabels) {
		$page->js .= $config->twig->render('util/sweetalert.twig', ['type' => 'success', 'title' => 'Success', 'msg' => 'Your labels have printed', 'icon' => 'fa fa-print fa-2x']);
	}
