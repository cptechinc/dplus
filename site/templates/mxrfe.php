<?php

$rm = strtolower($input->requestMethod());
$values = $input->$rm;

$filter = $modules->get('FilterXrefItemMxrfe');
$mxrfe  = $modules->get('XrefMxrfe');
$mxrfe->init_field_attributes_config();

if ($values->action) {
	$mxrfe->process_input($input);
	$session->redirect($page->redirectURL($input), $http301 = false);
}

$page->show_breadcrumbs = false;
$page->body .= $config->twig->render('items/mxrfe/bread-crumbs.twig', ['page' => $page]);

if ($session->response_xref) {
	$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
}

if ($values->mnfrID) {
	$mnfrID = $values->text('mnfrID');
	$vendor = $mxrfe->vendor($mnfrID);

	if ($values->mnfritemID) {
		$mnfritemID = $values->text('mnfritemID');
		$itemID = $values->text('itemID');
		$xref = $mxrfe->get_create_xref($mnfrID, $mnfritemID, $itemID);
		$qnotes = $modules->get('QnotesItemMxrfe');

		if (!$xref->isNew()) {
			/**
			 * Show alert that MXRFE is locked if
			 *  1. MXRFE isn't new
			 *  2. The MXRFE has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			 if (!$mxrfe->lockrecord($xref)) {
				$msg = "MXRFE ". $mxrfe->get_recordlocker_key($xref) ." is being locked by " . $mxrfe->recordlocker->get_locked_user($mxrfe->get_recordlocker_key($xref));
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "MXRFE ".$mxrfe->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		$page->body .= $config->twig->render('items/mxrfe/item/form/display.twig', ['page' => $page, 'mxrfe' => $mxrfe, 'vendor' => $vendor, 'xref' => $xref, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('items/mxrfe/item/form/js.twig', ['page' => $page, 'mxrfe' => $mxrfe]);
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		if (!$xref->isNew()) {
			if ($session->response_qnote) {
				$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			}
			$page->body .= $config->twig->render('items/mxrfe/item/notes/notes.twig', ['page' => $page, 'xref' => $xref, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('items/mxrfe/item/notes/js.twig', ['page' => $page, 'xref' => $xref, 'qnotes' => $qnotes]);
		}
	} else {
		$filter->vendorid($mnfrID);
		$filter->apply_sortby($page);
		$xrefs = $filter->query->paginate($input->pageNum, $session->display);
		$page->body .= $config->twig->render('items/mxrfe/list/vendor/page.twig', ['page' => $page, 'mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor]);
	}
} else {
	$filter = $modules->get('FilterVendors');
	$filter->vendorid($mxrfe->vendorids());
	$filter->apply_sortby($page);
	$vendors = $filter->query->paginate($input->pageNum, $session->display);
	$page->body .= $config->twig->render('items/mxrfe/search/vendor/results.twig', ['page' => $page, 'vendors' => $vendors]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
	// $page->js   .= $config->twig->render('items/mxrfe/list/js.twig', ['page' => $page]);
}

$session->remove('response_xref');
$session->remove('response_qnote');

include __DIR__ . "/basic-page.php";
