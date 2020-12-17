<?php
	include_once('./itm-prepend.php');
	$cxm = $modules->get('XrefCxm');
	$filter_cxm = $modules->get('FilterXrefItemCxm');
	$html = $modules->get('HtmlWriter');
	$page->title = "CXM";

	if ($values->action) {
		$cxm->process_input($input);

		if ($cxm->xref_exists($custID, $custitemID)) {
			if ($session->response_xref && $session->response_xref->has_success()) {
				$session->redirect($page->itm_xrefs_cxmURL($itemID, $session->response_xref->key), $http301 = false);
			}
			$session->redirect($page->cxm_itemURL($custID, $custitemID), $http301 = false);
		} else {
			$session->redirect($page->itm_xrefs_cxmURL($itemID), $http301 = false);
		}
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->custitemID) {
		$custID = $input->get->text('custID');
		$custitemID = $input->get->text('custitemID');
		$page->headline = "ITM: $itemID CXM Item $custitemID for $custID";

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
		$page->body .= $config->twig->render('items/cxm/item/form.twig', ['page' => $page, 'item' => $item, 'cxm' => $cxm]);

		if (!$item->isNew()) {
			$qnotes = $modules->get('QnotesItemCxm');
			$page->body .= $html->div('class=mt-3', $html->h3('', 'Notes'));
			$page->body .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['page' => $page, 'item' => $item, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('items/cxm/item/notes/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
		}
		$page->js   .= $config->twig->render('items/cxm/item/form/js.twig', ['page' => $page, 'cxm' => $cxm, 'item' => $item, 'url_validate' => $pages->get('pw_template=cxm-validate')->httpUrl]);
	} else {
		$cxm->recordlocker->remove_lock();
		$filter_cxm->filter_input($input);
		$filter_cxm->apply_sortby($page);
		$page->headline = "ITEM: CXM Item $itemID";

		$items = $filter_cxm->query->paginate($input->pageNum, 0);

		$page->body .= $config->twig->render('items/itm/xrefs/cxm/list/header.twig', ['page' => $page, 'items' => $items, 'itemid' => $itemID]);
		$page->body .= $config->twig->render('items/itm/xrefs/cxm/list/list.twig', ['page' => $page, 'response' => $session->response_xref, 'items' => $items, 'recordlocker' => $cxm->recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		$page->js   .= $config->twig->render('items/itm/xrefs/cxm/list/js.twig', ['page' => $page]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->remove('response_xref');
	include __DIR__ . "/basic-page.php";
