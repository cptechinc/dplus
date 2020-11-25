<?php
	include_once('./itm-prepend.php');
	$kim = $modules->get('Kim');
	$kim->init_configs();
	$kitID = $values->text('itemID');

	if ($values->action) {
		$kim->process_input($input);
		$session->redirect($page->itm_kitURL($kitID));
	}

	if ($session->response_kim) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_kim]);
	}

	/**
	 * Show alert that Kit is locked if
	 *  1. Kit isn't new
	 *  2. The Kit has a record lock
	 *  3. Userid does not match the lock
	 * Otherwise if not locked, create lock
	 */
	if (!$kim->lockrecord($kitID)) {
		$msg = "ITM Item $kitID is being locked by " . $kim->recordlocker->get_locked_user($kitID);
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $kitID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$page->body .= $html->div('class=mb-3');
	}

	$page->headline = "Kit for $kitID";
	$item = $itm->get_item($kitID);
	$kit = $kim->get_kit($kitID);

	$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent('pw_template=itm')]);

	if ($input->get->component) {
		$itemID = $input->get->text('component');
		$component = $kim->component->new_get_component($kitID, $itemID);
		$page->headline = $itemID == 'new' ? "ITM Kit - $kitID" : "ITM Kit - $kitID - $itemID";
		$page->body .= $config->twig->render('items/itm/kit/component/page.twig', ['page' => $page, 'kim' => $kim, 'kit' => $kit, 'component' => $component]);
		$page->js   .= $config->twig->render('items/itm/kit/component/js.twig', ['page' => $page, 'kim' => $kim, 'kit' => $kit, 'component' => $component]);
	} else {
		$page->body .= $config->twig->render('mki/kim/kit/page.twig', ['page' => $page, 'kim' => $kim, 'kit' => $kit]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	if ($session->response_kim) {
		$session->remove('response_kim');
	}

	include __DIR__ . "/basic-page.php";
