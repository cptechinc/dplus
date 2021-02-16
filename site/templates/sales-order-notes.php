<?php
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$qnotes = $modules->get('QnotesSalesOrder');
	$lookup_orders = new Dplus\CodeValidators\Mso();

	if ($input->requestMethod('POST')) {
		$response = $qnotes->process_input($input);
		$session->redirect($page->fullURL->getURL());
	} else {
		if ($input->get->ordn) {
			$ordn = $input->get->text('ordn');

			if ($lookup_orders->order($ordn) || $lookup_orders->invoice($ordn)) {
				if ($lookup_orders->lookup_salesorder($ordn)) {
					$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				} elseif ($lookup_orders->lookup_saleshistory($ordn)) {
					$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
					$qnotes = $modules->get('QnotesSalesHistory');
				}
				$page->title = "Sales Order #$ordn Notes";

				if ($session->response_qnote) {
					$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
					$session->remove('response_qnote');
				}
				$page->body .= $config->twig->render('sales-orders/sales-order/notes/qnotes-page.twig', ['page' => $page, 'user' => $user, 'ordn' => $ordn, 'order' => $order, 'qnotes_so' => $qnotes]);
				$page->body .= $config->twig->render('sales-orders/sales-order/notes/note-modal.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes_so' => $qnotes]);
				$page->js   .= $config->twig->render('sales-orders/sales-order/notes/js.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes' => $qnotes]);

				$page->search_notesURL = $pages->get('pw_template=msa-noce-ajax')->url;
				$page->body .= $config->twig->render('msa/noce/ajax/notes-modal.twig', []);
				$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page]);

				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

			} else {
				$page->headline = "Sales Order #$ordn could not be found";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
			}
		} else {
			$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
		}
	}

	include __DIR__ . "/basic-page.php";
