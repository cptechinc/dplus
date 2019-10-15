<?php
	$config_salesorders = $modules->get('SalesOrdersConfig');
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count() || SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
			$page->print = true;
			$page->title = "Sales Order #$ordn";
			$type = 'order';

			if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
				$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				$order_items = SalesOrderDetailQuery::create()->filterByOrdernumber($ordn)->find();
			} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
				$type = 'history';
				$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
				$order_items = SalesHistoryDetailQuery::create()->filterByOrdernumber($ordn)->find();
			}

			$customer = CustomerQuery::create()->findOneByCustid($order->custid);

			$barcoder = $modules->get('BarcodeMaker');
			$dpluscustomer = $pages->get('/config/customer/');

			if (!$page->is_pdf()) {
				$page->pdfURL = "$page->url?ordn=$ordn&download=pdf";
				$page->body .= $config->twig->render("sales-orders/sales-order/print/print-actions.twig", ['page' => $page]);
				$page->body .= $html->div('class=clearfix mb-3');
			}

			$page->body .= $config->twig->render("sales-orders/sales-$type/print/header.twig", ['page' => $page, 'customer' => $customer, 'order' => $order, 'dpluscustomer' => $dpluscustomer, 'barcoder' => $barcoder]);
			$page->body .= $html->div('class=clearfix mb-3');
			$page->body .= $config->twig->render("sales-orders/sales-$type/print/items.twig", ['page' => $page, 'order' => $order, 'order_items' => $order_items]);
			$page->body .= $config->twig->render("sales-orders/sales-$type/print/totals.twig", ['page' => $page, 'order' => $order]);
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}


	if ($page->print) {
		if (!$page->is_pdf()) {
			$page->show_title = false;
			$pdfmaker = $modules->get('PdfMaker');
			$pdfmaker->set_fileID("order-$order->ordernumber");
			$pdfmaker->set_filetype('order');
			$pdfmaker->set_url($page->get_pdfURL());
			$pdfmaker->generate_pdf();
		}
	}

	if ($input->get->download) {
		header("Content-type:application/pdf");
		// It will be called downloaded.pdf
		header("Content-Disposition:attachment;filename=".$pdfmaker->get_filename());
		// The PDF source is in original.pdf
		readfile($config->directory_webdocs.$pdfmaker->get_filename());
	} else {
		if ($page->print) {
			$page->show_title = true;

			if ($page->is_pdf()) {
				$page->show_title = false;
			}

			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
