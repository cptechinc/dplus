<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;

		if ($input->get->shipfromID) {
			$shipfromID = $input->get->text('shipfromID');
			$load_shipfrom = $load_vendor;
			$query_shipfrom = VendorShipfromQuery::create()->filterByVendorid($vendorID)->filterByShipfromid($shipfromID);

			if ($load_shipfrom->shipfrom_exists()) {
				$shipfrom = $load_shipfrom->get_shipfrom();
				$page->title = "$shipfrom->shipfromid";
				$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
				$page->title = "$vendor->name Ship-from: $shipfrom->id";
				$refreshurl = $page->get_vi_vendorshipfromURL($vendorID, $shipfrom->shipfromid);
				$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'refreshurl' => $refreshurl]);
				$function_pages = [];
				$toolbar = '';
				$header  = $config->twig->render('vendors/vi/shipfrom/header.twig', ['page' => $page, 'shipfrom' => $shipfrom, 'con' => $con]);

				//$page->body .= $config->twig->render('vendors/ci/ci-links.twig', ['page' => $page]);
				$page->body .= "<div class='row'>";
					$page->body .= $html->div('class=col-sm-2', $toolbar);
					$page->body .= $html->div('class=col-sm-10', $header);
				$page->body .= "</div>";

				$page->body .= $config->twig->render('vendors/vi/vendor/purchase-orders-panel.twig', ['page' => $page, 'resultscount' => $shipfrom->countPurchaseOrders(), 'purchaseorders' => $load_vendor->get_purchaseorders(), 'purchase_orders_list' => $load_vendor->get_purchaseordersURL()]);
			} else {
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "$vendorID Ship-to $shipfromID does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check if shipfromID is correct"]);
			}
		} else {
			$page->title = "Select a $vendor->name Ship-from";
			$query_shipfrom = VendorShipfromQuery::create()->filterByVendorid($vendorID);

			if ($query_shipfrom->count() == 1) {
				$shipfrom = $query_shipfrom->findOne();
				$session->redirect($page->get_vi_vendorshipfromURL($vendorID, $shipfrom->shipfromid));
			} elseif ($query_shipfrom->count() != 0) {
				$shipfroms = $query->shipfrom->find();
				//$page->body .= $config->twig->render('vendors/ci/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
				$page->body .= $config->twig->render('vendors/vi/shipfrom/shipfrom-list.twig', ['page' => $page, 'vendor' => $vendor, 'shipfroms' => $shipfroms]);
			} else {
				$page->headline = $page->title = "Ship Froms could not be loaded";
				$page->body = $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'refreshurl' => $refreshurl]);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "No Ship Froms Available", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No Ship Froms Available"]);
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
