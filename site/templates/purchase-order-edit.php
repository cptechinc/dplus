<?php
	$epo  = $modules->get('PurchaseOrderEdit');
	$html = $modules->get('HtmlWriter');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$epo->process_input($input);
		if ($values->text('action') == 'exit') {
			$url = $page->po_viewURL($values->text('ponbr'));
		} else {
			$url = $page->po_editURL($values->text('ponbr'));
		}
		$session->redirect($url, $http301 = false);
	}

	if ($session->response_epo) {
		$page->body .= $config->twig->render('purchase-orders/purchase-order/edit/response.twig', ['response' => $session->response_epo]);
	}

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$page->ponbr = $ponbr;
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($epo->exists($ponbr)) {
			$epo->init_configs();
			$page->headline = "Editing Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();

			if ($epo->exists_editable($ponbr)) {
				$po_edit = $epo->get_editable_header($ponbr);
				$po_readonly = $epo->get_purchaseorder($ponbr);
				$page->search_itemsURL = $pages->get('pw_template=itm-search')->url;
				$page->search_vendorsURL = $pages->get('pw_template=vi-search')->url;
				$page->search_countriesURL = $pages->get('pw_template=lookup-country-codes')->url;
				$page->search_shipfromURL =  $pages->get('pw_template=vi-shipfrom')->url;
				$page->body .= $config->twig->render('purchase-orders/purchase-order/edit/edit.twig', ['page' => $page, 'epo' => $epo, 'po' => $po_edit, 'po_readonly' => $po_readonly]);
				$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/js.twig', ['page' => $page, 'epo' => $epo]);
				$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/lookup/js.twig', ['page' => $page]);
				// $config->scripts->append(hash_templatefile('scripts/lib/datatables.js'));
				// $config->styles->append(hash_templatefile('styles/lib/datatables.css'));

				if ($values->q) {
					$q = $values->text('q');
					$epo->request_itemsearch($q);
					$results = PricingQuery::create()->findBySessionid(session_id());
					$page->body .= $config->twig->render('purchase-orders/purchase-order/edit/lookup/results.twig', ['page' => $page, 'results' => $results, 'q' => $q]);
				}
			} else {
				if ($input->get->load) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $ponbr can not be loaded for editing"]);
				} else {
					$epo->request_po_edit($ponbr);
					$page->fullURL->query->set('load', 1);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}
			}
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body .= $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'EPO';
		$page->body .= $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
