<?php
include_once('./itm-prepend.php');

$item = $itm->get_item($itemID);

$filter = $modules->get('FilterXrefItemMxrfe');
$mxrfe = $modules->get('XrefMxrfe');
$mxrfe->init_field_attributes_config();

if ($values->action) {
	$mxrfe->process_input($input);
	$session->redirect($page->redirectURL($input), $http301 = false);
}

if ($values->mnfritemID) {
	$mnfrID = $values->text('mnfrID');
	$mnfritemID = $values->text('mnfritemID');
	$itemID = $values->text('itemID');
	$xref = $mxrfe->get_create_xref($mnfrID, $mnfritemID, $itemID);
	$qnotes = $modules->get('QnotesItemMxrfe');

	if (!$xref->isNew()) {
		 if (!$mxrfe->lockrecord($xref)) {
			$msg = "MXRFE ". $mxrfe->get_recordlocker_key($xref) ." is being locked by " . $mxrfe->recordlocker->get_locked_user($mxrfe->get_recordlocker_key($xref));
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "MXRFE ".$mxrfe->get_recordlocker_key($xref)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$page->body .= $html->div('class=mb-3');
		}
	}

	$page->body .= $config->twig->render('items/itm/xrefs/mxrfe/form/display.twig', ['page' => $page, 'mxrfe' => $mxrfe, 'xref' => $xref, 'qnotes' => $qnotes]);
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
	$mxrfe->recordlocker->remove_lock();
	$filter->filter_input($input);
	$filter->apply_sortby($page);
	$xrefs = $filter->query->paginate($input->pageNum, $session->display);
	$page->body .= $config->twig->render('items/itm/xrefs/mxrfe/list/display.twig', ['page' => $page, 'item' => $item, 'mxrfe' => $mxrfe, 'xrefs' => $xrefs]);
}


include __DIR__ . "/basic-page.php";
