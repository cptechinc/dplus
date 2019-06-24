<?php
	use ProcessWire\WireHttp;

	$warehousepacking = $modules->get('WarehousePacking');
	$warehousepacking->set_sessionID(session_id());
	$whsesession = $warehousepacking->get_whsesession();
	$warehouse   = $warehousepacking->get_warehouseconfig();
	$http = new WireHttp();

	if ($input->get->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));
		$warehousepacking->init_cartoncount();

		if (SalesOrderQuery::create()->orderExists($ordn)) {
			$warehousepacking->set_ordn($ordn);

			if ($input->get->linenbr) {
				if ($input->requestMethod('POST') || $input->get->text('action') == 'add-carton') {
					$warehousepacking->handle_barcodeaction($input);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}

				$page->linenbr = $input->get->int('linenbr');
				$page->carton = $warehousepacking->get_cartoncount();
				$warehousepacking->init_packsalesorderdetail_line($page->linenbr);
				$packitem = $warehousepacking->get_packsalesorderdetail_line($page->linenbr);

				if ($packitem->is_item_serialized()) {
					$inventoryresults = InvsearchQuery::create()->findByItemid(session_id(), $packitem->itemid);
					$packed_items = WhseitempackQuery::create()->filterBySessionidOrderLinenbr(session_id(), $ordn, $page->linenbr)->find();
					$page->body .= $config->twig->render('warehouse/packing/packing-form-serialized.twig', ['page' => $page, 'warehousepacking' => $warehousepacking, 'packitem' => $packitem]);
					$page->body .= $config->twig->render('warehouse/packing/packed-items-serialized.twig', ['page' => $page, 'warehousepacking' => $warehousepacking, 'packitem' => $packitem, 'packed_items' => $packed_items]);
					$page->body .= $config->twig->render('warehouse/packing/item-availability-modal.twig', ['packitem' => $packitem, 'inventoryresults' => $inventoryresults,]);
				} elseif ($packitem->is_item_lotted()) {

				} else {
					$page->body .= $config->twig->render('warehouse/packing/packing-form.twig', ['page' => $page, 'warehousepacking' => $warehousepacking, 'packitem' => $packitem]);
				}
			} else {
				$http->get("127.0.0.1".$pages->get('template=redir, redir_file=sales-order')->url."?action=get-order-notes&ordn=$ordn&sessionID=".session_id());
				$page->body = $config->twig->render('warehouse/packing/order-notes.twig', ['page' => $page, 'notes' => $warehousepacking->get_packingnotes()]);
				$page->body .= $config->twig->render('warehouse/packing/select-line-form.twig', ['page' => $page, 'warehousepacking' => $warehousepacking]);
			}
		} else {
			$page->body = $config->twig->render('warehouse/packing/status.twig', ['page' => $page, 'message' => "Error finding Sales Order # $ordn"]);
		}
	} else {
		$page->formurl = $page->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/packing/sales-order-form.twig', ['page' => $page]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/pack-order.js'));

	include __DIR__ . "/basic-page.php";
