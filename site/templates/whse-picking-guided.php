<?php
	$modules->get('DpagesMwm')->init_picking();
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');

	// CHECK If Sales Order is Provided
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$pickorder = PickSalesOrderQuery::create()->findOneByOrdernbr($ordn);

		if ($whsesession->is_orderinvalid()) { // Validate Sales Order Number
			$page->formurl = $page->parent->child('template=redir')->url;
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Sales Order #$ordn Not Found"]);
			$page->body .= "<div class='form-group'></div>";
			$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
			// CHECK the Order is not finished
		} elseif (!$whsesession->has_bin() && !$whsesession->is_orderfinished()) {
			// CHECK if the order is unpickable because it's on hold | verified | invoiced
			if ($whsesession->is_orderonhold() || $whsesession->is_orderverified() || $whsesession->is_orderinvoiced() || $whsesession->is_ordernotfound() || (!$config_inventory->allow_negativeinventory && $whsesession->is_ordershortstocked())) {
				$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
				// VALIDATE if wrong picking function is being used
			} elseif ($whsesession->is_usingwrongfunction()) {
				$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
			} else { // SHOW STARTING BIN FORM
				$page->title = 'Choose Starting Bin';
				$page->formurl = $page->parent->child('template=redir')->url;
				$page->body = $config->twig->render('warehouse/picking/bin-form.twig', ['page' => $page]);
				$page->body .= $config->twig->render('warehouse/picking/bins-modal.twig', ['page' => $page, 'warehouse' => $warehouse]);
			}
		} elseif ($whsesession->needs_functionprompt()) {
			if ($input->get->removeprompt) {
				$whsesession->set_functionprompt(false);
				$whsesession->save();
				$page->fullURL->query->remove('removeprompt');
				$session->redirect($page->fullURL->getUrl());
			} else {
				$page->cancelURL = $page->parent->child('template=redir')->url.'?action=cancel-order';
				$page->fullURL->query->set('removeprompt', 'removeprompt');
				$page->removepromptURL = $page->fullURL->getUrl();
				$page->body = $config->twig->render('warehouse/picking/function-prompt.twig', ['page' => $page, 'whsesession' => $whsesession]);
			}
		} else { // PICKING FUNCTION
			// CHECK If there are details to pick
			if (PickSalesOrderDetailQuery::create()->filterBySessionidOrder(session_id(), $ordn)->count() > 0) {
				// TODO
				$page->formurl = $page->parent->child('template=redir')->url;
				$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionidOrder(session_id(), $ordn);

				if ($input->requestMethod('POST')) {
					$action = $input->post->text('action');
					switch ($action) {
						case 'add-barcode':
							$barcode   = $input->post->text('barcode');
							$palletnbr = $input->post->int('palletnbr');
							$pickitem->add_barcode($barcode, $palletnbr);
							break;
						case 'edit-barcode':
							$barcode   = $input->post->text('barcode');
							$palletnbr = $input->post->int('palletnbr');
							$recordnbr = $input->post->int('recordnbr');
							$qty       = $input->post->int('qty');
							$pickitem->edit_barcode_qty($barcode, $palletnbr, $recordnbr, $qty);
							echo $dpluso->getLastExecutedQuery();
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

				$picked_barcodes = WhseitempickQuery::create()->get_order_pickeditems(session_id(), $ordn, $pickitem->itemid);
				$jsconfig = array(
					'pickitem' => array(
						'item'          => $pickitem->get_jsconfigarray(),
						'url_changebin' => "$page->formurl?action=select-bin&binID=&page=".$page->fullURL->getUrl()
					)
				);

				$page->body =  $config->twig->render('warehouse/picking/picking-form.twig',    ['page' => $page, 'whsesession' => $whsesession, 'pickitem' => $pickitem, 'dpluso', $dpluso]);
				$page->body .= $config->twig->render('warehouse/picking/picked-barcodes.twig', ['page' => $page, 'picked_barcodes' => $picked_barcodes]);
				$page->body .= $config->twig->render('warehouse/picking/item-info-modal.twig', ['pickitem' => $pickitem]);
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);

				// Or check if sales order is finished
			} elseif ($whsesession->is_orderfinished()) {
				$page->body .= $config->twig->render('warehouse/picking/finished-order.twig', ['page' => $page, 'pickorder' => $pickorder]);
			} else { // NO ITEMS TO PICK
				if ($whsesession->is_orderfinished() || $whsesession->is_orderexited()) {
					WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
				}
				$modules->get('DplusRequest')->self_request($page->parent->child('template=redir')->url."?action=start-pick&sessionID=".session_id());
				$page->formurl = $page->parent->child('template=redir')->url;
				$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
				$page->body .= '<div class="form-group"></div>';
				$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
			}
		}
	} else {
		$modules->get('DplusRequest')->self_request($page->parent->child('template=redir')->url."?action=start-pick&sessionID=".session_id());
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}

	$config->scripts->append(hash_templatefile('scripts/warehouse/pick-order.js'));

	include __DIR__ . "/basic-page.php";
