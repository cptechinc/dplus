<?php
	$vxm = $modules->get('XrefVxm');

	if ($input->get->vendorID) {
	} elseif ($input->get->itemID) {
	} elseif ($input->get->search) {
	} else {
		$page->body .= $config->twig->render('items/vxm/vxm-search.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
