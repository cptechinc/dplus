<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$html = $modules->get('HtmlWriter');

	$modules->get('DpagesMwm')->init_picking();
	$pickingsession = $modules->get('WarehousePicking');
	$pickingsession->set_sessionID(session_id());
	$pickingsession->set_ordn($ordn);

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');
	$config_picking   = $modules->get('ConfigsWarehousePicking');

	// CHECK If there are details to pick
	$nbr_pickinglines = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->count();

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

				if ($input->get->sublinenbr !== null) {
					$sublinenbr = $input->get->int('sublinenbr');
					$pickingsession->set_sublinenbr($sublinenbr);
					$pickitem = $pickingsession->get_picksalesorderdetail();
					$page->title = "Picking Order # $ordn Line $linenbr";
					$page->title .= $sublinenbr > 0 ? " Subline $sublinenbr" : '';

					// If item is stocked, get Inventory for that item
					if (!$pickitem->is_item_nonstock()) {
						$modules->get('DplusRequest')->self_request($pages->get('template=redir,redir_file=inventory')->url."?action=inventory-search&scan=$pickitem->itemid&sessionID=".session_id());
					}

					$picked_barcodes = WhseitempickQuery::create()->filterByBin('PACK', Criteria::ALT_NOT_EQUAL)->filterBySessionidOrder(session_id(), $ordn)->filterByLinenbrSublinenbr($pickitem->linenbr, $pickitem->sublinenbr)->find();

					$picked_barcodes_packbin = WhseitempickQuery::create()->filterByBin('PACK')->filterBySessionidOrder(session_id(), $ordn)->filterByLinenbrSublinenbr($pickitem->linenbr, $pickitem->sublinenbr)->find();
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

					$page->body .= $config->twig->render('warehouse/picking/unguided/picking-details.twig', ['pickitem' => $pickitem, 'pickingsession' => $pickingsession]);

					if ($session->pickerror) {
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $session->pickerror]);
						$page->body .= '<div class="form-group"></div>';
						$session->remove('pickerror');
					}

					if ($pickitem->is_item_serialized()) {
						$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-serialized-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Serial Numbers Scanned');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-serialized.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Previously Picked Serial Numbers');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-serialized.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes_packbin, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
					} elseif ($pickitem->is_item_lotted()) {
						$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-lotted-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Lot Numbers Scanned');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-lotted.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Previously Picked Lot Numbers');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes-lotted.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes_packbin, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
					} else {
						$page->body .= $config->twig->render('warehouse/picking/unguided/barcode-form.twig', ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'config_picking' => $config_picking, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Barcodes Scanned');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
						$page->body .= $html->h3('class=text-center', 'Previously Picked Barcodes');
						$page->body .= $config->twig->render('warehouse/picking/unguided/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes_packbin, 'pickitem' => $pickitem, 'pickingsession' => $pickingsession]);
					}

					$page->body .= $config->twig->render('warehouse/picking/bins-modal.twig', ['warehouse' => $warehouse]);
					$inventoryresults = InvsearchQuery::create()->findByItemid(session_id(), $pickitem->itemid);
					$page->body .= $config->twig->render('warehouse/picking/unguided/item-availability-modal.twig', ['inventoryresults' => $inventoryresults, 'pickitem' => $pickitem, 'warehouse' => $warehouse, 'pickingsession' => $pickingsession]);
					$page->body .= $config->twig->render('warehouse/picking/item-info-modal.twig', ['pickitem' => $pickitem]);
					$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
					// If there's only one subline for that Order Line, redirect to subline 0
				} elseif (PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->filterByLinenbr($linenbr)->count() == 1) {
					$page->fullURL->query->set('sublinenbr', 0);
					$session->redirect($page->fullURL->getUrl());
				} else { // Choose Subline
					$page->title = "Choose a Subline for Line # $linenbr";
					$items_unpicked = PickSalesOrderDetailQuery::create()->get_order_sublines_unpicked(session_id(), $ordn, $linenbr);
					$items_picked   = PickSalesOrderDetailQuery::create()->get_order_sublines_picked(session_id(), $ordn, $linenbr);
					$page->formurl  = $pages->get('template=redir,redir_file=inventory')->url;

					$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
					$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items_unpicked' => $items_unpicked, 'items_picked' => $items_picked, 'pickingsession' => $pickingsession]);
				}
			} elseif ($input->get->scan) { // TODO
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

				if (PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->filterByItemid($item->itemid)->count() == 1) {
					$linenbr = PickSalesOrderDetailQuery::create()->get_orderlinenbr(session_id(), $ordn, $item->itemid);
					$sublinenbr = PickSalesOrderDetailQuery::create()->get_ordersublinenbr(session_id(), $ordn, $item->itemid);
					$page->fullURL->query->set('linenbr', $linenbr);
					$page->fullURL->query->set('sublinenbr', $sublinenbr);

					// IF THERE'S ONLY ONE BIN AUTO ADD THE SCANNED ITEM
					// 5/23/2019 ROGER SAYS ONLY AUTOADD WITH SERIALIZED
					// TODO handle with picking session
					if ($bincount == 1 && $item->is_serialized()) {
						$pickitem = PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->filterByLinenbrSublinenbr($linenbr, $sublinenbr)->findOne();
						$picking_master->filterBySessionidOrder($this->sessionID, $this->ordn);
						$picking_master->filterByLinenbrSublinenbr($this->linenbr, $this->sublinenbr);
						$barcode = $item->is_lotted() || $item->is_serialized() ? $item->lotserial : $item->itemid;
						$pickingsession->add_pickedbarcode($pickitem, $barcode, 1, 1, $item->bin);
					} else {
						$session->pickerror = "That item is in multiple bins";
					}
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				} else {
					$items_unpicked = PickSalesOrderDetailQuery::create()->get_order_lines_unpicked(session_id(), $ordn);
					$items_picked   = PickSalesOrderDetailQuery::create()->get_order_lines_picked(session_id(), $ordn);
					$page->formurl  = $pages->get('template=redir,redir_file=inventory')->url;

					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Attention!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No Items match '$scan'"]);
					$page->body .= '<div class="form-group"></div>';
					$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
					$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items_unpicked' => $items_unpicked, 'items_picked' => $items_picked, 'pickingsession' => $pickingsession]);
				}
			} else {
				$items_unpicked = PickSalesOrderDetailQuery::create()->get_order_lines_unpicked(session_id(), $ordn);
				$items_picked   = PickSalesOrderDetailQuery::create()->get_order_lines_picked(session_id(), $ordn);
				$page->formurl  = $pages->get('template=redir,redir_file=inventory')->url;

				if ($whsesession->had_picksucceeded()) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'success', 'title' => "Success!", 'iconclass' => 'fa fa-floppy-o fa-2x', 'message' => $whsession->status]);
					$page->body .= '<div class="form-group"></div>';
				}
				$page->body .= $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
				$page->body .= $config->twig->render('warehouse/picking/unguided/select-item-form.twig', ['page' => $page, 'items_unpicked' => $items_unpicked, 'items_picked' => $items_picked, 'pickingsession' => $pickingsession, 'dpluso' => $dpluso]);
			}
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
