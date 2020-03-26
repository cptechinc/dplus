<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$html = $modules->get('HtmlWriter');
	$modules->get('DpagesMwm')->init_picking();
	$pickingsession = $modules->get('WarehousePicking');
	$pickingsession->set_sessionID(session_id());
	$pickingsession->set_ordn($ordn);

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$whsesession->setFunction(whsesession::PICKING_UNGUIDED);
	$whsesession->save();
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');
	$config_picking   = $modules->get('ConfigsWarehousePicking');
	$page->title = "Picking Order #$ordn";

	// CHECK If there are details to pick
	$lines_query = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn);

	if ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'ordn' => $ordn]);
	} elseif ($lines_query->count() > 0) {

		if ($input->requestMethod('POST')) {
			$pickingsession->handle_barcodeaction($input);
			$session->redirect($page->fullURL->getUrl());
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
				} else {
					$session->remove('verify_whseitempick_items');
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
				}
			} else {

				$query_phys = WhseitemphysicalcountQuery::create();
				$query_phys->filterBySessionid(session_id());
				$query_phys->filterByScan($scan);
				$query_phys->filterByBin('PACK', Criteria::ALT_NOT_EQUAL);
				$query_phys->find();

				if ($query_phys->count() == 1) {
					$item = $query_phys->findOne();

					if ($item->has_error()) {
						$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error searching '$scan'", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $item->get_error()]));
						$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
					} else {
						$page->body .= $config->twig->render('warehouse/picking/provalley/scan/add-scanned-item-form.twig', ['page' => $page, 'item' => $item, 'scan' => $scan]);
					}
				} elseif ($query_phys->count() == 0) {
					$page->body .= $html->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => '0 items found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No items found for '$scan'"]));
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
				} else {
					$physicalitems = $query_phys->groupBy('itemid')->find();
					$page->body .= $config->twig->render('warehouse/picking/provalley/scan/select-item-list.twig', ['page' => $page, 'items' => $physicalitems]);
				}
			}
		} else {
			$page->body .= $config->twig->render('warehouse/picking/provalley/scan/scan-form.twig', ['page' => $page]);
		}

		$page->body .= $config->twig->render('warehouse/picking/provalley/line-items.twig', ['page' => $page, 'lineitems' => $lines_query->find()]);

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
