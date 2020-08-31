<?php
	$epo  = $modules->get('PurchaseOrderEdit');
	$html = $modules->get('HtmlWriter');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$epo->process_input($input);
		$url = $page->fullURL->getUrl();

		if ($session->response_epo) {
			if ($values->text('action') == 'create-po' && $session->response_epo->has_success()) {
				$url = $page->po_editURL($session->response_epo->ponbr);
			}
		}

		$session->redirect($url, $http301 = false);
	}

	if ($session->response_epo) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_epo]);
		$session->remove('response_epo');
	}

	if ($input->get->ponbr) {
		if ($input->get->text('ponbr') == 'new') {
			$page->headline = "EPO: Create New PO";

			if ($input->get->vendorID) {
				$validate_vendor = $modules->get('ValidateVendorId');
				$vendorID = $input->get->text('vendorID');

				if ($validate_vendor->validate($vendorID)) {
					$vendor = $epo->get_vendor($vendorID);
					$page->body = $config->twig->render('purchase-orders/epo/create-po-form.twig', ['page' => $page, 'vendor' => $vendor]);
				} else {
					$session->redirect("$page->url?ponbr=new", $http301 = false);
				}
			} else {
				$filter_vendors = $modules->get('FilterVendors');
				$filter_vendors->init_query($user);

				$query = VendorQuery::create();

				if ($input->get->q) {
					$q = strtoupper($input->get->text('q'));
					$page->headline = "EPO: Searching for '$q'";
					$filter_vendors->filter_search($q);
				}

				$filter_vendors->apply_sortby($page);
				$query = $filter_vendors->get_query();
				$vendors = $query->paginate($input->pageNum, 10);

				$page->searchURL = "$page->url?ponbr=new";
				$page->body .= $config->twig->render('purchase-orders/epo/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
			}
		} else {
			$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
			$page->ponbr = $ponbr;
			$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

			if ($epo->exists($ponbr)) {
				$session->redirect($page->po_editURL($ponbr), $http301 = false);
			} else {
				$page->headline = "PO #$ponbr not found";
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger','iconclass' => 'fa fa-warning fa-2x', 'title' => "PO #$ponbr not found", 'message' => "Check if the Purchase Order Number is correct"]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('purchase-orders/epo/lookup-form.twig', ['page' => $page]);
			}
		}
	} else {
		$page->title = 'EPO';
		$page->body .= $config->twig->render('purchase-orders/epo/lookup-form.twig', ['page' => $page]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
