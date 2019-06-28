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

	if ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'ordn' => $ordn]);
	} elseif ($nbr_pickinglines > 0) {
		if ($nbr_pickinglines == 1 && !$input->get->linenbr) {
			$page->fullURL->query->set('linenbr', 1);
			$session->redirect($page->fullURL->getUrl(), $http301 = false);
		} else {
			if ($input->get->linenbr) {
				$linenbr = $input->get->int('linenbr');
				$pickingsession->set_linenbr($linenbr);
				$page->formurl = $page->parent->child('template=redir')->url;
				$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);

				// If item is stocked, get Inventory for that item
				if (!$pickitem->is_item_nonstock()) {
					$http->get("127.0.0.1".$pages->get('template=redir,redir_file=inventory')->url."?action=inventory-search&scan=$pickitem->itemid&sessionID=".session_id());
				}
				$picked_barcodes = WhseitempickQuery::create()->get_order_pickeditems(session_id(), $ordn, $pickitem->itemid);
				$inventory_master = InvsearchQuery::create();
				$pickingsession->insert_barcode_itemID($pickitem->itemid);
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
					$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-serialized.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
				} elseif ($pickitem->is_item_lotted()) {
					$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-lotted-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
					$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-lotted.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
				} else {
					$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'config_picking' => $config_picking, 'pickingsession' => $pickingsession]);
					$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
				}

				$page->body .= $config->twig->render('warehouse/picking/bins-modal.twig', ['warehouse' => $warehouse]);
				$inventoryresults = InvsearchQuery::create()->findByItemid(session_id(), $pickitem->itemid);
				$page->body .= $config->twig->render('warehouse/picking/unguided/item-availability-modal.twig', ['inventoryresults' => $inventoryresults, 'pickitem' => $pickitem, 'warehouse' => $warehouse, 'pickingsession' => $pickingsession]);
				$page->body .= $config->twig->render('warehouse/picking/item-info-modal.twig', ['pickitem' => $pickitem]);
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);

			} elseif ($input->get->scan) {
				$scan = $input->get->text('scan');
				$page->fullURL->query->remove('binID');
				$page->fullURL->query->remove('scan');
				$bincount = 0;

				if (InvsearchQuery::create()->countByLotserial(session_id(), $scan)) {
					$item = InvsearchQuery::create()->get_lotserial(session_id(), $scan);
					$bincount = InvsearchQuery::create()->count_itembins_lotserial(session_id(), $item->itemid, $item->lotserial);
				} elseif (InvsearchQuery::create()->countByItemid(session_id(), $scan)){
					$item = InvsearchQuery::create()->findOneByItemid(session_id(), $scan);
					$bincount = InvsearchQuery::create()->count_itembins_itemid(session_id(), $item->itemid);
				}

				if (PickSalesOrderDetailQuery::create()->countBySessionidOrderItemid(session_id(), $ordn, $item->itemid)) {
					$linenbr = PickSalesOrderDetailQuery::create()->get_orderlinenbr(session_id(), $ordn, $item->itemid);
					$page->fullURL->query->set('linenbr', $linenbr);

					// IF THERE'S ONLY ONE BIN AUTO ADD THE SCANNED ITEM
					// 5/23/2019 ROGER SAYS ONLY AUTOADD WITH SERIALIZED
					// TODO handle with picking session
					if ($bincount == 1 && $item->is_serialized()) {
						$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);
						$barcode = $item->is_lotted() || $item->is_serialized() ? $item->lotserial : $item->itemid;
						$pickingsession->add_pickedbarcode($pickitem, $barcode, 1, $item->bin);
					} else {
						$session->pickerror = "That item is in multiple bins";
					}
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				} else {
					$items_unpicked = PickSalesOrderDetailQuery::create()->get_order_lines_unpicked(session_id(), $ordn);
					$items_picked   = PickSalesOrderDetailQuery::create()->get_order_lines_picked(session_id(), $ordn);
					$page->formurl = $pages->get('template=redir,redir_file=inventory')->url;

					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Attention!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No Items match '$scan'"]);
					$page->body .= '<div class="form-group"></div>';
					$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
					$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items_unpicked' => $items_unpicked, 'items_picked' => $items_picked, 'pickingsession' => $pickingsession]);
				}
			} else {
				$items_unpicked = PickSalesOrderDetailQuery::create()->get_order_lines_unpicked(session_id(), $ordn);
				$items_picked   = PickSalesOrderDetailQuery::create()->get_order_lines_picked(session_id(), $ordn);
				$page->formurl = $pages->get('template=redir,redir_file=inventory')->url;

				if ($whsesession->had_picksucceeded()) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'success', 'title' => "Success!", 'iconclass' => 'fa fa-floppy-o fa-2x', 'message' => $whsession->status]);
					$page->body .= '<div class="form-group"></div>';
				}

				$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items_unpicked' => $items_unpicked, 'items_picked' => $items_picked, 'pickingsession' => $pickingsession]);
			}
		}
	} else { // NO ITEMS TO PICK
		$whsesession->setStatus("There are no detail lines available to pick for Order # $ordn");
		if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
			WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
		}
		//==$http->get("127.0.0.1".$page->parent->child('template=redir')->url."?action=start-pick-unguided&sessionID=".session_id());
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		$page->body .= '<div class="form-group"></div>';
		$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}
