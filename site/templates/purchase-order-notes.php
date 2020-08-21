<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();


	$qnotes = $modules->get('QnotesPo');
	$page->title = "Notes";

	if ($values->action) {
		$ponbr = PurchaseOrder::get_paddedponumber($values->text('ponbr'));
		$qnotes->process_input($input);
		$session->redirect($page->view_notesURL($ponbr));
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->show_breadcrumbs = false;
			$page->body .= $config->twig->render('purchase-orders/purchase-order/bread-crumbs.twig', ['page' => $page, 'ponbr' => $ponbr]);
			$purchaseorder = $query->findOne();
			$page->headline = "PO #$ponbr Notes";
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes/qnotes.twig', ['page' => $page, 'db' => $db_dplusdata, 'ponbr' => $ponbr, 'purchaseorder' => $purchaseorder, 'qnotes' => $qnotes]);
			$page->search_notesURL = $pages->get('pw_template=msa-noce-ajax')->url;
			$page->js .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page]);
			$page->js .= $config->twig->render('purchase-orders/purchase-order/qnotes/js.twig');
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
