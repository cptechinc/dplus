<?php
	$cxm = $modules->get('XrefCxm');
	$modules->get('DpagesMso')->init_cxm_hooks();
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$custID = $input->$rm->text('custID');
		$custitemID = $input->$rm->text('custitemID');
		$cxm->process_input($input);

		if ($cxm->xref_exists($custID, $custitemID)) {
			if ($session->response_xref && $session->response_xref->has_success()) {
				$session->redirect($page->cxm_customerURL($custID, $session->response_xref->key), $http301 = false);
			}
			$session->redirect($page->cxm_itemURL($custID, $custitemID), $http301 = false);
		} else {
			$session->redirect($page->cxm_customerURL($custID), $http301 = false);
		}
	}

	$html = $modules->get('HtmlWriter');
	$filter_cxm = $modules->get('FilterXrefItemCxm');
	$qnotes = $modules->get('QnotesItemCxm');
	$validate = $modules->get('ValidateCxm');

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/cxm/bread-crumbs.twig', ['page' => $page]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/cxm/response.twig', ['response' => $session->response_xref]);
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($session->response_pdm) {
		$page->body .= $config->twig->render('mso/pdm/response-alert.twig', ['response' => $session->response_pdm]);
	}

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		if (!$validate->custid($custID)) {
			$session->redirect($page->url."?q=$custID", $http301 = false);
		}
		$customer = CustomerQuery::create()->findOneById($custID);

		if ($input->get->custitemID) {
			$custitemID = $input->get->text('custitemID');
			$page->title = "CXM: $custID Item $custitemID";
			$item = $cxm->get_create_xref($custID, $custitemID);

			if (!$item->isNew()) {
				/**
				 * Show alert that CXM is locked if
				 *  1. CXM isn't new
				 *  2. The CXM has a record lock
				 *  3. Userid does not match the lock
				 * Otherwise if not locked, create lock
				 */
				 if (!$cxm->lockrecord($item)) {
					$msg = "CXM ". $cxm->get_recordlocker_key($item) ." is being locked by " . $cxm->recordlocker->get_locked_user($cxm->get_recordlocker_key($item));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM ".$cxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			} else {
				$cxm->recordlocker->remove_lock();
				$page->headline = "CXM: New Item X-Ref for $custID";
				if ($custitemID != 'new') {
					$item->setCustitemid($custitemID);
					$msg = "CXM for Customer $custID Customer Item ID $custitemID does not exist";
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with CXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}

			$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
			$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
			$page->body .= $config->twig->render('items/cxm/item/form/display.twig', ['page' => $page, 'item' => $item, 'cxm' => $cxm, 'qnotes' => $qnotes]);

			if (!$item->isNew()) {
				$qnotes = $modules->get('QnotesItemCxm');
				$page->body .= $html->div('class=mt-3', $html->h3('', 'Notes'));
				$page->body .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['page' => $page, 'item' => $item, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('items/cxm/item/notes/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
			}

			$page->js   .= $config->twig->render('items/cxm/item/form/js.twig', ['page' => $page, 'item' => $item, 'cxm' => $cxm, 'url_validate' => $pages->get('pw_template=cxm-validate')->httpUrl]);
		} else {
			$cxm->recordlocker->remove_lock();
			$page->headline = "CXM: Customer $customer->name";
			$filter_cxm->filter_input($input);
			$q = $values->q ? $values->text('q') : '';

			if ($values->q) {
				$page->headline = "CXM: Search '$q' for Customer $customer->name";
				$filter_cxm->search($values->text('q'));
			}
			$filter_cxm->apply_sortby($page);

			$items = $filter_cxm->query->paginate($input->pageNum, $session->display);
			$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
			$page->body .= $config->twig->render('items/cxm/cxm-links.twig', ['page' => $page]);
			$page->body .= $config->twig->render('items/cxm/search/item/customer/form.twig', ['page' => $page, 'q' => $q, 'custID' => $custID, 'q' => $q]);
			$page->body .= $config->twig->render('items/cxm/item-list-header.twig', ['page' => $page, 'heading' => $items->getNbResults() . " CXM Items for $customer->name"]);
			$page->body .= $config->twig->render('items/cxm/item-list.twig', ['page' => $page, 'cxm' => $cxm, 'response' => $session->response_xref, 'items' => $items, 'custID' => $custID, 'recordlocker' => $cxm->recordlocker, 'db' => $db_dpluso]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			$page->js   .= $config->twig->render('items/cxm/list/js.twig', ['page' => $page]);
		}
	} elseif ($input->get->itemID) {
		$cxm->recordlocker->remove_lock();
		$itemID = $input->get->text('itemID');
		$filter_cxm->filter_query($input);
		$filter_cxm->apply_sortby($page);
		$items = $filter_cxm->query->paginate($input->pageNum, $session->display);

		$page->headline = "CXM: Item $itemID";
		$page->body .= $html->h3('', $items->getNbResults() ." CXM Items for $itemID");
		$page->body .= $config->twig->render('items/cxm/cxm-links.twig', ['page' => $page]);
		$page->body .= $config->twig->render('items/cxm/item-list.twig', ['page' => $page, 'response' => $session->response_xref, 'items' => $items, 'recordlocker' => $cxm->recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
	}  else {
		$cxm->recordlocker->remove_lock();
		$q = $input->get->q ? strtoupper($input->get->text('q')) : '';
		$filter = $modules->get('FilterCustomers');
		$filter->init_query($user);
		$filter->custid($cxm->custids());
		$filter->search($q);
		$filter->apply_sortby($page);
		$query = $filter->get_query();
		$customers = $query->paginate($input->pageNum, $session->display);

		$page->searchURL = $page->url;
		$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
		$page->body .= $config->twig->render('items/cxm/search/customer/results.twig', ['page' => $page, 'customers' => $customers]);
		$page->body .= $config->twig->render('items/cxm/new-cxm-modal.twig', ['page' => $page]);
		$page->js   .= $config->twig->render('items/cxm/search/customer/js.twig', ['page' => $page]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	}
	$session->remove('response_xref');
	$session->remove('response_pdm');

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
