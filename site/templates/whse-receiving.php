<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);

	$html = $modules->get('HtmlWriter');

	if ($input->get->ponbr) {
		$ponbr = $input->get->text('ponbr');
		$page->title = "Receiving PO # $ponbr";
		$purchaseorder = PurchaseOrderQuery::create()->findOneByPonbr($ponbr);

		$page->body .= $config->twig->render('warehouse/inventory/receiving/po-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
		$page->body .= $config->twig->render('warehouse/inventory/receiving/po-items.twig', ['page' => $page, 'items' => $purchaseorder->get_items()]);
	} else {
		$page->body .= $config->twig->render('warehouse/inventory/receiving/po-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
