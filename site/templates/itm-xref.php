<?php
	$itm = $modules->get('Itm');
	//$itm_costing = $modules->get('ItmCosting');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {

		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
