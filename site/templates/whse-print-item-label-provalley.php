<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$page->formurl = $page->parent->child('template=redir')->url;

	$printing = $modules->get('WhseLabelPrinterProvalley');
	$printing->init();

	$page->addHook('Page::print_itemlabelURL', function($event) {
		$p = $event->object;
		$item = $event->arguments(0);
		$url = new Purl\Url($p->url);
		$url->query->set('scan', $p->scan);
		$url->query->set('itemID', $item->itemid);
		$url->query->set($item->get_itemtypeproperty(), $item->get_itemidentifier());
		$url->query->set('binID', $item->binID);
		$event->return = $url->getUrl();
	});

	if ($values->action) {
		$printing->process_input($input);
		$url = $page->url;
		if ($values->text('action') == 'print-labels') {
			$url = $page.create_newlabelURL($values->text('itemID'));
		}
		$session->redirect($url, $http301 = false);
	}

	if ($values->text('label') == 'new') {
		$item = new Invsearch();
		$item->setSessionid(session_id());
		$item->setItemid($values->text('itemID'));
		$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/label-form.twig', ['page' => $page, 'item' => $item]);
		$page->js   .= $config->twig->render('warehouse/inventory/print-item-label/provalley/js.twig', ['page' => $page]);
	}

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Print Item Label: Results for '$scan'";
		$inventory = InvsearchQuery::create();
		$inventory->filterBySessionid(session_id());

		if ($inventory->count() == 0) {
			$session->redirect($page->url, $http301 = false);
		}

		if ($values->itemID) {
			$inventory->filterByItemid($values->text('itemID'));

			if ($values->lotnbr) {
				$inventory->filterByLotserial($values->text('lotnbr'));
			}
			if ($values->binID) {
				$inventory->filterByBin($values->text('binID'));
			}
			$inventory->count();
		}

		if ($inventory->count() == 1) {
			$item = $inventory->findOne();
			$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/label-form.twig', ['page' => $page, 'item' => $item]);
			$page->js   .= $config->twig->render('warehouse/inventory/print-item-label/provalley/js.twig', ['page' => $page]);
		}

		if ($inventory->count() > 1) {
			$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id());
			$items = InvsearchQuery::create()->findDistinctItems(session_id());
			$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/inventory-results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
		}
	} elseif ($values->label == false) {
		$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/item-form.twig', ['page' => $page]);
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
