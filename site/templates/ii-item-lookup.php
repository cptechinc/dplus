<?php
	$exists = false;

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		$query = ItemsearchQuery::create();
		$query->filterActive();
		$query->filterByOrigintype(Itemsearch::ORIGINTYPE_ITEM);

		if ($query->filterByItemid($itemID)->count()) {
			$exists = true;
			$item = $query->findOne();

		} elseif ($input->get->custID) {
			$custID = $input->get->text('custID');
			$query->clear();
			$query->filterByOrigintype([Itemsearch::ORIGINTYPE_CUSTOMER, Itemsearch::ORIGINTYPE_ITEM]);
			$query->filterByOriginid($custID);

			if ($query->filterByItemid($q)->count()) {
				$exists = true;
				$item = $query->findOne();
			}
		}

		if ($exists) {
			$response = array(
				'error' => false,
				'exists' => true,
				'itemID' => $item->itemid
			);
		} else {
			$msg = "No item with the itemID $itemID has been found";

			if ($input->get->custID) {
				$msg .= " while also using the customer X-ref for $custID";
			}

			$response = array(
				'error' => false,
				'exists' => false,
				'itemID' => false,
				'message' => $msg
			);
		}

		$page->body = json_encode($response);
	} else {
		$response = array(
			'error' => true,
			'exists' => false,
			'itemID' => false,
			'message' => "No Item ID was sent with request"
		);
		$page->body = json_encode($response);
	}
