<?php
	$html = $modules->get('HtmlWriter');
	$vxm = $modules->get('XrefVxm');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$vendorID = $input->$rm->text('vendorID');
		$vendoritemID = $input->$rm->text('vendoritemID');
		$vxm->process_input($input);

		if ($vxm->vendors_item_exists($vendorID, $vendoritemID)) {
			$session->redirect($pages->vxm_vendors_itemURL($vendorID, $vendoritemID));

		} else {
			$session->redirect($pages->vxm_vendorURL($vendorID));
		}
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->vendorID) {
		$vendorID = $input->get->text('vendorID');
		$load_vendor = $modules->get('ViLoadVendorShipfrom');
		$load_vendor->set_vendorID($vendorID);
		$vendor = $load_vendor->get_vendor();

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/vxm/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);

		if (!$load_vendor->vendor_exists()) {
			$session->redirect($page->url."?q=$vendorID");
		}

		if ($input->get->vendoritemID) {
			$vendoritemID = $input->get->text('vendoritemID');
			if ($vxm->vendors_item_exists($vendorID, $vendoritemID)) {
				$item = $vxm->get_vendoritem($vendorID, $vendoritemID);
				$unitsofm = UnitofMeasurePurchaseQuery::create()->find();
				$page->title = $page->headline = "VXM: Item $vendoritemID for $vendorID";
				$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
				$page->body .= $config->twig->render('items/vxm/vendor/item.twig', ['page' => $page, 'item' => $item, 'vendor' => $vendor, 'unitsofm' => $unitsofm]);
				$page->js .= $config->twig->render('items/vxm/vendor/item/js.twig', ['item' => $item]);
			} elseif ($vendoritemID == 'new') {
				$item = new ItemXrefVendor();
				$item->setVendorid($vendorID);
				$unitsofm = UnitofMeasurePurchaseQuery::create()->find();
				$page->title = $page->headline = "VXM: Creating New Item for $vendorID";
				$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
				$page->body .= $config->twig->render('items/vxm/vendor/item.twig', ['page' => $page, 'item' => $item, 'vendor' => $vendor, 'unitsofm' => $unitsofm]);
				$page->js .= $config->twig->render('items/vxm/vendor/item/js.twig', ['item' => $item]);
			} else {
				$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'vendor Item Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$vendoritemID was not found"]);
				$page->body .= $config->twig->render('items/vxm/vendor/item-list.twig', ['page' => $page, 'items' => $items, 'vendorID' => $vendorID]);
			}
		} else {
			$page->headline = "VXM: Vendor $vendor->name";
			$items = $vxm->get_vendoritems($vendorID);
			$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
			$page->body .= $html->h3('', "VXM Items for $vendor->name");
			$page->body .= $config->twig->render('items/vxm/vendor/item-list.twig', ['page' => $page, 'items' => $items, 'vendorID' => $vendorID]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->count()]);
		}
	} elseif ($input->get->itemID) {
		$itemID = $input->get->text('itemID');
		$items = $vxm->get_ouritemitems($itemID);

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/vxm/bread-crumbs.twig', ['page' => $page]);

		if ($items->count()) {
			$page->headline = "VXM: Item $itemID";
			$page->body .= $html->h3('', "VXM Items for $itemID");
			$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
			$page->body .= $config->twig->render('items/vxm/vendor/item-list.twig', ['page' => $page, 'items' => $items, 'vendorID' => $vendorID]);
		} else {

		}
	} elseif ($input->get->search) {
		$q = $input->get->text('q');
		$searchtype = $input->get->text('search');

		if ($searchtype == 'vendors') {
			$exact_query = VendorQuery::create();

			if ($exact_query->filterByVendorid($q)->count() == 1) {
				$session->redirect($pages->vxm_vendorURL($q));
			} else {
				$page->headline = "VXM: Searching vendors for '$q'";
				$search_vendors = $modules->get('FilterVendors');
				$search_vendors->init_query($user);
				$search_vendors->filter_search($q);
				$search_vendors->apply_sortby($page);
				$query = $search_vendors->get_query();
				$vendors = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/vxm/search/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
			}
		} elseif ($searchtype == 'items') {

		}
	} else {
		$page->body .= $config->twig->render('items/vxm/vxm-search.twig', ['page' => $page]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
