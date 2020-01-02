<?php
	$modules->get('DpagesMvi')->init_vipage();
	$html = $modules->get('HtmlWriter');

	if ($input->get->vendorID) {
		$vendorID = $input->get->text('vendorID');
		$load_vendor = $modules->get('ViLoadVendorShipfrom');
		$load_vendor->set_vendorID($vendorID);

		if ($load_vendor->vendor_exists()) {
			$vendor = $load_vendor->get_vendor();
			$page->headline = "VI: $vendor->name";

			$toolbar = $config->twig->render('vendors/vi/vendor/toolbar.twig', ['page' => $page, 'vendorID' => $vendor->vendorid]);
			$header  = $config->twig->render('vendors/vi/vendor/header.twig', ['page' => $page, 'vendor' => $vendor]);

			$page->body = "<div class='row'>";
				$page->body .= $html->div('class=col-sm-2', $toolbar);
				$page->body .= $html->div('class=col-sm-10', $header);
			$page->body .= "</div>";

			$page->body .= $config->twig->render('vendors/vi/vendor/purchase-orders-panel.twig', ['page' => $page, 'resultscount' => $vendor->countPurchaseOrders(), 'purchaseorders' => $load_vendor->get_purchaseorders(), 'purchase_orders_list' => $load_vendor->get_purchaseordersURL()]);
			$page->body .= $config->twig->render('vendors/vi/vendor/invoices-panel.twig', ['page' => $page, 'resultscount' => $vendor->countApInvoices(), 'invoices' => $load_vendor->get_invoices(), 'purchase_history_list' => $load_vendor->get_invoicesURL()]);
		} else {
			$page->searchURL = $page->url;
			$page->headline = "Vendor $vendorID Not Found";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->headline, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check the Vendor ID is correct"]);
			$page->body .= $html->div('class=mb-3');
			$page->body = $config->twig->render('vendors/search-form.twig', ['page' => $page]);
		}
	} else {
		$query = VendorQuery::create();
		$exact_query = VendorQuery::create();

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			if ($exact_query->filterByVendorid($q)->count() == 1) {
				$session->redirect($page->get_vi_vendorURL($q));
			}

			$page->headline = "VI: Searching for '$q'";
			$col_vendorid = Vendor::get_aliasproperty('vendorid');
			$col_name = Vendor::get_aliasproperty('name');
			$columns = array($col_vendorid, $col_name);
			$query->search_filter($columns, strtoupper($q));
		}

		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$tablecolumn = Vendor::get_aliasproperty($orderbycolumn);
			$query->sortBy($tablecolumn, $sort);
		}

		$vendors = $query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('vendors/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
	}

	include __DIR__ . "/basic-page.php";
