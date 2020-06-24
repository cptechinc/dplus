<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$validate_po = $modules->get('ValidatePurchaseOrderNbr');
	$receiving = $modules->get('Receiving');
	$m_print = $modules->get('PrintLabelItemReceiving');

	if ($values->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));

		if ($validate_po->validate($ponbr)) {
			if ($values->action) {
				$m_print->process_input($input);
			}

			if ($session->response_print) {
				$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_print]);
				$session->remove('response_print');
			}

			$receiving->set_ponbr($ponbr);
			$po = $receiving->get_purchaseorder();
			$thermal_labels = ThermalLabelFormatQuery::create();
			$whse_printers = WhsePrinterQuery::create();

			if ($values->linenbr) {
				$linenbr = $values->int('linenbr');
				$po_line = $po->get_receivingitem($linenbr);
				$lotreceived = $receiving->get_receiving_item($linenbr, $values->text('lotserial'), $values->text('binID'));
				echo $db_dplusdata->getLastExecutedQuery();
				if (!$values->lotserial || $values->text('lotserial') == 'all') {
					$lotreceived->setLotserial('all');
				}

				if (!$m_print->session_exists()) {
					$m_print->request_label_init($lotreceived);
				}

				$labelsession = $m_print->get_session();

				if ($labelsession->isNew()) {
					$labelsession->setSessionid(session_id());
				}

				$labelsession->setWhse($whsesession->whseid);
				$labelsession->setItemid($po_line->itemid);
				$labelsession->setBin($lotreceived->bin);
				$labelsession->setLotserial($lotreceived->lotserial);
				$labelsession->save();

				$page->title = "Print Item Label for $labelsession->itemid " . $values->text('lotserial');

				$page->body .= $config->twig->render('warehouse/inventory/print-item-label/receiving/form.twig', ['page' => $page, 'm_print' => $m_print, 'labelsession' => $labelsession, 'receiveditem' => $lotreceived]);
				$page->body .= $config->twig->render('warehouse/inventory/print-item-label/labels-modal.twig', ['formats' => $m_print->get_labelformats()]);
				$page->body .= $config->twig->render('warehouse/inventory/print-item-label/printers-modal.twig', ['printers' => $m_print->get_printers()]);
			} else {
				$page->body .= $config->twig->render('warehouse/inventory/print-item-label/receiving/po-items.twig', ['page' => $page, 'ponbr' => $ponbr, 'items' => $po->get_receivingitems()]);
			}
		} else {
			$page->headline = "PO #$ponbr could not be found";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error! PO Number $ponbr not found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check if the Order Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/print-item-label.js'));

	include __DIR__ . "/basic-page.php";
