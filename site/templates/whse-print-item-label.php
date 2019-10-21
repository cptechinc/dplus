<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$page->print = false;
	$binID = $input->get->text('binID');
	$page->show_masterpack = $modules->get("ConfigsWarehouseLabelPrinting")->show_masterpack;

	$page->addHook('Page::print_itemlabelURL', function($event) {
		$p = $event->object;
		$item = $event->arguments(0);
		$url = new Purl\Url($p->parent->child('template=redir')->url);
		$url->query->set('action', 'init-label-print');
		$url->query->set('itemID', $item->itemid);
		$url->query->set($item->get_itemtypeproperty(), $item->get_itemidentifier());
		$url->query->set('binID', $item->bin);
		$event->return = $url->getUrl();
	});

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Find Item Inquiry for $scan";
		$inventory = InvsearchQuery::create();
		$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id());
		$items = InvsearchQuery::create()->findDistinctItems(session_id());
		$page->body = $config->twig->render('warehouse/inventory/print-item-label/inventory-results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
	} elseif (!empty($input->get->serialnbr) | !empty($input->get->lotnbr) | !empty($input->get->itemID)) {
		if ($input->get->lotnbr) {
			$lotnbr = $input->get->text('lotnbr');
			$input->get->scan = $page->scan = $lotnbr;
			$resultscount = InvsearchQuery::create()->countByLotserial(session_id(), $lotnbr, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->get_lotserial(session_id(), $lotnbr, $binID) : false;
		} if ($input->get->serialnbr) {
			$serialnbr = $input->get->text('serialnbr');
			$input->get->scan = $page->scan = $serialnbr;
			$resultscount = InvsearchQuery::create()->countByLotserial(session_id(), $serialnbr, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->get_lotserial(session_id(), $lotnbr, $binID) : false;
		} elseif ($input->get->itemID) {
			$itemID = $input->get->text('itemID');
			$input->get->scan = $page->scan = $itemID;
			$resultscount = InvsearchQuery::create()->countByItemid(session_id(), $itemID, $binID);
			$item = $resultscount == 1 ? InvsearchQuery::create()->findOneByItemid(session_id(), $itemID, $binID) : false;
		}

		if ($resultscount == 1) {
			$thermal_labels = ThermalLabelFormatQuery::create();
			$whse_printers = WhsePrinterQuery::create();

			if (LabelPrintSessionQuery::create()->filterBySessionid(session_id())->count()) {
				$labelsession = LabelPrintSessionQuery::create()->findOneBySessionid(session_id());
			} else {
				$labelsession = new LabelPrintSession();
				$labelsession->setSessionid(session_id());
				$labelsession->setItemid($item->itemid);
				$labelsession->setBin($item->bin);
				$labelsession->setLotserial($item->lotserial);
				$labelsession->setWhse($whsesession->whseid);
			}
			$page->formurl = $page->parent->child('template=redir')->url;
			$page->body    =  $config->twig->render('warehouse/inventory/print-item-label/label-form.twig', ['page' => $page, 'label' => $labelsession, 'thermal_labels' => $thermal_labels, 'printers' => $whse_printers]);
			$page->body    .= $config->twig->render('warehouse/inventory/print-item-label/labels-modal.twig', ['formats' => $thermal_labels->get_formats(), 'item' => $item]);
			$page->body    .= $config->twig->render('warehouse/inventory/print-item-label/printers-modal.twig', ['printers' => $whse_printers->find()]);
		} else {
			$inventory = InvsearchQuery::create();
			$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id());
			$items = InvsearchQuery::create()->findDistinctItems(session_id());
			$page->body = $config->twig->render('warehouse/inventory/print-item-label/inventory-results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
		}
	} else {
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body .= $config->twig->render('warehouse/inventory/print-item-label/item-form.twig', ['page' => $page]);
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/print-item-label.js'));

	include __DIR__ . "/basic-page.php";
