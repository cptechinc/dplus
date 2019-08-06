<?php
	use ProcessWire\WireHttp;

	$warehousepacking = $modules->get('WarehousePacking');
	$warehousepacking->set_sessionID(session_id());
	$whsesession = $warehousepacking->get_whsesession();
	$warehouse   = $warehousepacking->get_warehouseconfig();
	$http = new WireHttp();
	$html = $modules->get('HtmlWriter');

	if ($input->get->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));
		$warehousepacking->set_ordn($ordn);
		$warehousepacking->init_cartoncount();

		if (SalesOrderQuery::create()->orderExists($ordn)) {
			$page->title = "Packing Order #$ordn";
			$warehousepacking->set_ordn($ordn);

			if ($input->get->finish) {
				$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
				$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
				$whse_printers = WhsePrinterQuery::create();

				if (LabelPrintSessionQuery::create()->filterBySessionid(session_id())->count()) {
					$labelsession = LabelPrintSessionQuery::create()->findOneBySessionid(session_id());
				} else {
					$labelsession = new LabelPrintSession();
					$labelsession->setSessionid(session_id());
					$labelsession->setItemid($ordn);
				}
				$page->formurl = $page->get_redirURL();
				$page->body = $config->twig->render('warehouse/packing/finish/print-form.twig', ['page' => $page, 'ordn' => $ordn, 'labelsession' => $labelsession, 'printers' => $whse_printers, 'whsesession' => $whsesession]);
				$whse_printers->clear();
				$page->body .= $config->twig->render('warehouse/inventory/print-item-label/printers-modal.twig', ['printers' => $whse_printers->find()]);
			} else {
				if ($input->requestMethod('POST') || $input->get->text('action') == 'add-box') {
					$warehousepacking->handle_barcodeaction($input);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}

				//$http->get("127.0.0.1".$pages->get('template=redir, redir_file=sales-order')->url."?action=get-order-notes&ordn=$ordn&sessionID=".session_id());
				$page->body = $config->twig->render('warehouse/packing/order-notes.twig', ['page' => $page, 'notes' => $warehousepacking->get_packingnotes()]);
				$page->body .= $html->div('class=mb-3');
				if ($session->packerror) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $warehousepacking->get_error()]);
					$page->body .= '<div class="form-group"></div>';
					$session->remove('packerror');
				}
				$page->body .= $config->twig->render('warehouse/packing/packing-form.twig', ['page' => $page, 'warehousepacking' => $warehousepacking]);
				$page->body .= $html->h3('Items');
				$page->body .= $config->twig->render('warehouse/packing/order-items.twig', ['page' => $page, 'warehousepacking' => $warehousepacking]);
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
