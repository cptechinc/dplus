<?php
	$modules->get('DplusoPagesWarehouse')->init_picking();
	$pickingsession = $modules->get('DplusoWarehousePicking');
	$pickingsession->set_sessionID(session_id());
	$pickingsession->set_ordn($ordn);

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('WarehouseInventoryConfig');
	$config_picking   = $modules->get('WarehousePickingConfig');

	// CHECK If there are details to pick
	$nbr_pickinglines = PickSalesOrderDetailQuery::create()->countBySessionidOrder(session_id(), $ordn);
	if ($nbr_pickinglines > 0) {
		if ($nbr_pickinglines == 1) {
			$page->fullURL->query->set('linenbr', 1);

			$session->redirect($page->fullURL->getUrl(), $http301 = false);
		} else {
			if ($input->get->linenbr) {
				$linenbr = $input->get->int('linenbr');
				$pickingsession->set_linenbr($linenbr);
				$page->formurl = $page->parent->child('template=redir')->url;
				$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);
				$http->get($pages->get('template=redir,redir_file=inventory')->httpUrl."?action=inventory-search&scan=$pickitem->itemid&sessionID=".session_id());
				$picked_barcodes = WhseitempickQuery::create()->get_order_pickeditems(session_id(), $ordn, $pickitem->itemid);

				$jsconfig = array(
					'pickitem' => array(
						'item'          => $pickingsession->get_pickitem_jsconfig(),
						'url_changebin' => "$page->formurl?action=select-bin&binID=&page=".$page->fullURL->getUrl()
					)
				);

				if ($input->requestMethod('POST')) {
					$pickingsession->handle_barcodeaction($input);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}

				$page->body .=  $config->twig->render('warehouse/picking/unguided/picking-details.twig', ['pickitem' => $pickitem, 'pickingsession' => $pickingsession]);

				if ($session->pickerror) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $session->pickerror]);
					$page->body .= '<div class="form-group"></div>';
					$session->remove('pickerror');
				}

				if ($pickitem->is_item_serialized()) {
					$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-serialized-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
				} else {
					$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'config_picking' => $config_picking]);
				}

				$page->body .= $config->twig->render('warehouse/picking/bins-modal.twig', ['warehouse' => $warehouse]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);

			} elseif ($input->get->scan) {
				$scan = $input->get->text('scan');
				$page->fullURL->query->remove('binID');
				$page->fullURL->query->remove('scan');

				if (InvsearchQuery::create()->countByLotserial(session_id(), $scan)) {
					$item = InvsearchQuery::create()->get_lotserial(session_id(), $scan);
				}

				if (InvsearchQuery::create()->countByItemid(session_id(), $scan)){
					$item = InvsearchQuery::create()->findOneByItemid(session_id(), $scan);
				}

				if (PickSalesOrderDetailQuery::create()->countBySessionidOrderItemid(session_id(), $ordn, $item->itemid)) {
					$linenbr = PickSalesOrderDetailQuery::create()->get_orderlinenbr(session_id(), $ordn, $item->itemid);
					$page->fullURL->query->set('linenbr', $linenbr);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}


			} else {
				$items = PickSalesOrderDetailQuery::create()->findBySessionidOrder(session_id(), $ordn);

				$page->formurl = $pages->get('template=redir,redir_file=inventory')->url;
				$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items' => $items, 'pickingsession' => $pickingsession]);
			}
		}
		// Or check if sales order is finished
	} elseif ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'pickorder' => $pickorder]);
	} else { // NO ITEMS TO PICK
		$whsesession->setStatus("There are no detail lines available to pick for Order # $ordn");
		if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
			WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
		}
		//$http->get($page->parent->child('template=redir')->httpUrl."?action=start-pick-unguided&sessionID=".session_id());
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		$page->body .= '<div class="form-group"></div>';
		$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}
