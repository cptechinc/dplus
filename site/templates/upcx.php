<?php
	$html = $modules->get('HtmlWriter');
	$upcx = $modules->get('XrefUpc');
	$filter_upcs = $modules->get('FilterXrefItemUpc');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$upcx->process_input($input);
		$code = $input->$rm->text('upc');
		$itemID = $input->$rm->text('itemID');

		if ($code) {
			$session->redirect($page->upcURL($code));
		} else {
			$session->redirect($page->upcURL($code));
		}
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->upc) {
		$code = $input->get->text('upc');
		$unitsofm = UnitofMeasurePurchaseQuery::create()->find();

		if ($upcx->upc_exists($code)) {
			$upc = $upcx->get_upc($code);
			$page->title = "UPCX: UPC $code";
		} else {
			$upc = new ItemXrefUpc();

			if ($input->get->itemID) {
				$itemID = $input->get->text('itemID');

				if ($upcx->validate_itemID($itemID)) {
					$page->title = "Adding UPC X-ref for $itemID";
					$upc->setItemid($itemID);
				} else {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID $itemID not found in the Item Master"]);
					$page->body .= $html->div('class=mb-3');
				}
			}

			if ($code == 'new') {
				$page->title = "Adding UPC X-ref";
			} else {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "UPC $code not found, you may create it below"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/upcx/bread-crumbs.twig', ['page' => $page, 'upc' => $upc]);
		$page->body .= $config->twig->render('items/upcx/upc-form.twig', ['page' => $page, 'upc' => $upc, 'unitsofm' => $unitsofm]);
		$url_validate = $pages->get('pw_template=upcx-validate')->httpUrl;
		$page->js .= $config->twig->render('items/upcx/js.twig', ['upc' => $upc, 'url_validate' => $url_validate]);
	} else {
		$itemID = $input->get->text('itemID');
		$filter_upcs->filter_query($input);
		$filter_upcs->apply_sortby($page);
		$upcs = $filter_upcs->query->find();

		if ($input->get->itemID) {
			if ($upcx->validate_itemID($itemID)) {
				$page->title = "UPCs for $itemID";
			}
		}

		$page->body .= $config->twig->render('items/upcx/upc-filters.twig', ['page' => $page, 'input' => $input]);
		$page->body .= $config->twig->render('items/upcx/upc-list.twig', ['page' => $page, 'upcs' => $upcs, 'itemID' => $itemID]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
