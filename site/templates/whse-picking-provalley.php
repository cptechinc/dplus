<?php
	use Propel\Runtime\ActiveQuery\Criteria;
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	$html = $modules->get('HtmlWriter');
	$modules->get('DpagesMwm')->init_picking();
	$pickingsession = $modules->get('PickingProvalley');
	$pickingsession->set_sessionID(session_id());
	$pickingsession->set_ordn($ordn);

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$whsesession->setFunction(whsesession::PICKING_UNGUIDED);
	$whsesession->save();
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$page->title = "Picking Order #$ordn";

	// CHECK If there are details to pick
	$lines_query = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn);

	if ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'ordn' => $ordn]);
	} elseif ($lines_query->count() > 0) {

		if ($values->action) {
			$pickingsession->handle_action($input);
			$session->redirect($page->fullURL->getUrl(), $http301 = false);
		}

		if ($session->pickingerror) {
			$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$session->pickingerror"]));
			$session->remove('pickingerror');
		}

		if ($whsesession->has_warning()) {
			$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Warning!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $whsesession->status]));
		} elseif ($whsesession->has_message()) {
			$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $whsesession->status]));
		}

		if ($input->get->scan) {
			$scan = $input->get->text('scan');

			if ($session->verify_whseitempick_items) {
				$query_pickeditems = WhseitempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id());
				$query_pickeditems->filterByBarcode($scan);
				$query_pickeditems->filterByRecordnumber($session->verify_whseitempick_items);
				$query_pickeditems->find();

				if ($query_pickeditems->count()) {
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/verify-whseitempick-lotserials.twig', ['page' => $page, 'scan' => $scan, 'items' => $query_pickeditems->find()]);
					$page->body .= $html->div('class=mb-3');
				} else {
					$session->remove('verify_whseitempick_items');
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
				}
			} else {
				$query_phys = $pickingsession->inventory->get_inventory_scan_query($scan, $includepack = false);

				if ($query_phys->count() == 1) {
					$item = $query_phys->findOne();

					if ($item->has_error()) {
						$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error searching '$scan'", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $item->get_error()]));
						$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
					} elseif ($pickingsession->validate_autosubmit($item)) {
						$pickingsession->auto_add_lotserial($item);
						$page->fullURL->query->remove('scan');
						$session->redirect($page->fullURL->getUrl());
					} else {
						$page->body .= $config->twig->render('warehouse/picking/provalley/scan/add-scanned-item-form.twig', ['page' => $page, 'm_picking' => $pickingsession, 'item' => $item, 'scan' => $scan]);
					}
				} elseif ($query_phys->count() == 0) {
					$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => '0 items found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No items found for '$scan'"]));
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
				} else {
					$physicalitems = $query_phys->groupBy('itemid')->find();
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/select-item-list.twig', ['page' => $page, 'm_picking' => $pickingsession, 'items' => $physicalitems]);
					$page->body .= $html->div('class=mb-3');
				}
			}
		} else {
			$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
		}

		$page->body .= $config->twig->render('warehouse/picking/provalley/line-items.twig', ['page' => $page, 'm_picking' => $pickingsession, 'lineitems' => $lines_query->find()]);

		if (!$input->get->scan) {
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('warehouse/picking/provalley/picking-actions.twig', ['page' => $page]);
		}

		if ($session->removefromline) {
			$page->js .= $config->twig->render('warehouse/picking/remove-line.js.twig', ['linenbr' => $session->removefromline]);
			$session->remove('removefromline');
		}
	} else { // NO ITEMS TO PICK
		$whsesession->setStatus("There are no detail lines available to pick for Order # $ordn");
		if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
			WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
		}
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		$page->body .= '<div class="form-group"></div>';
		$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}

	if ($session->printpicklabels) {
		$page->js .= $config->twig->render('util/sweetalert.twig', ['type' => 'success', 'title' => 'Success', 'msg' => 'Your labels have printed', 'icon' => 'fa fa-print fa-2x']);
	}
