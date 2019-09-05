<?php
	$module_qnotes_crud = $modules->get('QnotesCrud');
	$html = $modules->get('HtmlWriter');
	$cart = $modules->get('Cart');

	if ($input->requestMethod('POST')) {
		$response = $module_qnotes_crud->process_input_cart($input);
		$session->redirect($page->fullURL->getURL());
	} else {
		$customer = CustomerQuery::create()->findOneByCustid($cart->get_custid());
		$page->title = "Cart Notes for $customer->name";
		$page->body = $config->twig->render('cart/notes/qnotes-page.twig', ['page' => $page, 'items' => $cart->get_items(), 'cart' => $cart]);
		$page->body .= $config->twig->render('cart/notes/add-note-modal.twig', ['page' => $page]);
		$config->scripts->append(hash_templatefile('scripts/cart/cart-notes.js'));
	}

	include __DIR__ . "/basic-page.php";
