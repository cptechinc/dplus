<?php
	$config->so = ConfigSalesOrderQuery::create()->findOne();
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');
	$lookup_orders = new Dplus\CodeValidators\Mso();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if ($lookup_orders->order($ordn) || $lookup_orders->invoice($ordn)) {
			$page->print = true;
			$page->title = "Sales Order #$ordn";
			$type = 'order';

			if ($lookup_orders->order($ordn)) {
				$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
			} elseif ($lookup_orders->invoice($ordn)) {
				$type = 'history';
				$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
			}

			$customer = CustomerQuery::create()->findOneByCustid($order->custid);

			$barcoder = $modules->get('BarcodeMaker');
			$dpluscustomer = $pages->get('/config/customer/');

			if (!$page->is_pdf()) {
				$page->body .= $config->twig->render("sales-orders/sales-order/print/print-actions.twig", ['page' => $page]);
				$page->body .= $html->div('class=clearfix mb-3');
			}

			$page->body .= $config->twig->render("sales-orders/sales-$type/print/header.twig", ['page' => $page, 'customer' => $customer, 'order' => $order, 'dpluscustomer' => $dpluscustomer, 'barcoder' => $barcoder]);
			$page->body .= $html->div('class=clearfix mb-3');

			if ($config->twigloader->exists("sales-orders/sales-$type/print/$config->company/items.twig")) {
				$page->body .= $config->twig->render("sales-orders/sales-$type/print/$config->company/items.twig", ['page' => $page, 'config' => $config->so, 'order' => $order]);
			} else {
				$page->body .= $config->twig->render("sales-orders/sales-$type/print/items.twig", ['page' => $page, 'config' => $config->so, 'order' => $order]);
			}

			$page->body .= $config->twig->render("sales-orders/sales-$type/print/totals.twig", ['page' => $page, 'order' => $order]);
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
	}
	$pdfmaker = $modules->get('PdfMaker');
	$pdfmaker->set_fileID("order-$order->ordernumber");
	$pdfmaker->set_filetype('order');

	if ($input->get->download) {
		header("Content-type:application/pdf");
		// It will be called downloaded.pdf
		header("Content-Disposition:attachment;filename=".$pdfmaker->get_filename());
		// The PDF source is in original.pdf
		readfile($config->directory_webdocs.$pdfmaker->get_filename());
	} elseif (!$page->is_pdf()) {
		$page->show_title = false;
		$pdfmaker->set_url($page->get_printpdfURL());
		$pdfmaker->generate_pdf();
	} else {

	}

	if ($page->print) {
		$page->show_title = true;

		if ($page->is_pdf()) {
			$page->show_title = false;
		}
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
