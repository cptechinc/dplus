<?php
	$modules->get('DplusoPagesWarehouse')->init_picking();
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('WarehouseInventoryConfig');

	// CHECK If there are details to pick
	$nbr_pickinglines = PickSalesOrderDetailQuery::create()->countBySessionidOrder(session_id(), $ordn);
	if ($nbr_pickinglines > 0) {
		if ($nbr_pickinglines == 1) {

		} else {
			if ($input->get->linenbr) {
				$linenbr = $input->get->int('linenbr');
				$page->formurl = $page->parent->child('template=redir')->url;
				$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);
				$http->get($pages->get('template=redir,redir_file=inventory')->httpUrl."?action=inventory-search&scan=$pickitem->itemid&sessionID=".session_id());
				$picked_barcodes = WhseitempickQuery::create()->get_order_pickeditems(session_id(), $ordn, $pickitem->itemid);
				$jsconfig = array(
					'pickitem' => array(
						'item'          => $pickitem->get_jsconfigarray(),
						'url_changebin' => "$page->formurl?action=select-bin&binID=&page=".$page->fullURL->getUrl()
					)
				);

				if ($input->requestMethod('POST')) {
					$action = $input->post->text('action');

					switch ($action) {
						case 'add-barcode':
							$barcode   = $input->post->text('barcode');
							$palletnbr = $input->post->int('palletnbr');
							$binID     = $input->post->text('binID');
							$is_valid  = false;

							if ($pickitem->is_item_serialized() && $pickitem->has_picked_barcode($barcode)) {
								$session->pickerror = "Serial # $barcode has been picked for $pickitem->itemid";
							} else {

								if ($pickitem->is_item_serialized()) {
									$item = InvsearchQuery::create()->get_lotserial(session_id(), $barcode);
									$binID = $item->bin;
								} elseif ($pickitem->is_item_lotted()) {
									$item = InvsearchQuery::create()->get_lotserial(session_id(), $barcode, $binID);
									$binID = $item->bin;
								} else {
									$item = InvsearchQuery::create()->findOneByItemid(session_id(), $itemID, $binID);
								}

								if ($config->allow_negativeinventory) {
									$is_valid = true;
								} else {
									if ($item) {
										if (InvsearchQuery::create()->get_binqty(session_id(), $item, $binID) >= 0) {
											$is_valid = true;
										} else {
											$msg = "Insufficient " . strtoupper($item->get_itemtypepropertydesc())  . " " . $item->get_itemtypepropertydesc();
											$msg .= " from $binID";
											$session->pickerror = $msg;
										}
									} else {
										$session->pickerror = "Scan: $barcode doesn't match anything for $pickitem->itemid";
									}
								}

								if ($is_valid) {
									$pickitem->add_barcode($barcode, $palletnbr, $pickitem->linenbr, $binID);
								}
							}
							break;
						case 'edit-barcode':
							$barcode   = $input->post->text('barcode');
							$palletnbr = $input->post->int('palletnbr');
							$recordnbr = $input->post->int('recordnbr');
							$qty       = $input->post->int('qty');
							$pickitem->edit_barcode_qty($barcode, $palletnbr, $recordnbr, $qty);
							break;
						case 'delete-barcode':
							$barcode   = $input->post->text('barcode');
							$palletnbr = $input->post->int('palletnbr');
							$recordnbr = $input->post->int('recordnbr');
							$pickitem->delete_barcode_qty($barcode, $palletnbr, $recordnbr);
							break;
					}
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}
				$page->body .=  $config->twig->render('warehouse/picking/unguided/picking-details.twig', ['pickitem' => $pickitem]);

				if ($session->pickerror) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $session->pickerror]);
					$page->body .= '<div class="form-group"></div>';
					$session->remove('pickerror');
				}
				$page->body .=  $config->twig->render('warehouse/picking/unguided/barcode-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem]);
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);

			} elseif ($input->get->scan) {
				$scan = $input->get->text('scan');
				$page->fullURL->query->remove('binID');
				$page->fullURL->query->remove('scan');

				if (InvsearchQuery::create()->countByLotserial(session_id(), $scan)) {
					$item = InvsearchQuery::create()->get_lotserial(session_id(), $scan);
				}

				if (InvsearchQuery::create()->countByItemid(session_id(), $scan)){
					echo 'itemid';
				}

				if (PickSalesOrderDetailQuery::create()->countBySessionidOrderItemid(session_id(), $ordn, $item->itemid)) {
					$linenbr = PickSalesOrderDetailQuery::create()->get_orderlinenbr(session_id(), $ordn, $item->itemid);
					$page->fullURL->query->set('linenbr', $linenbr);

					if ($item->is_serialized()) {
						$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrderItemid(session_id(), $ordn, $item->itemid);
						$picking_master = WhseitempickQuery::create();

						$barcode = new Whseitempick();
						$barcode->setSessionid(session_id());
						$barcode->setOrdn($pickitem->ordn);
						$barcode->setItemid($pickitem->itemid);
						$barcode->setRecordnumber($picking_master->get_max_orderitem_recordnumber(session_id(), $pickitem->ordn, $pickitem->linenbr) + 1);
						$barcode->setLinenbr($pickitem->linenbr);
						$barcode->setPalletnbr(1);
						$barcode->setBarcode($item->lotserial);
						$barcode->setBin($item->bin);
						$barcode->setQty(1);
						$barcode->save();
					}
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}
			} else {
				$items = PickSalesOrderDetailQuery::create()->findBySessionidOrder(session_id(), $ordn);

				$page->formurl = $pages->get('template=redir,redir_file=inventory')->url;
				$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items' => $items]);
			}
		}
		// Or check if sales order is finished
	} elseif ($whsesession->is_orderfinished()) {
		$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'pickorder' => $pickorder]);
	} else { // NO ITEMS TO PICK
		if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
			WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
		}
		$http->get($page->parent->child('template=redir')->httpUrl."?action=start-pick-unguided&sessionID=".session_id());
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		$page->body .= '<div class="form-group"></div>';
		$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}
