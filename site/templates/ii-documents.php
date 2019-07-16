<?php
	include_once('./ii-include.php');

	if ($itemquery->count()) {
		$page->title = "$itemID Documents";

		$document_management = $modules->get('DocumentManagement');
		$documents = $document_management->get_itemdocuments($itemID);

		if ($input->get->document && $input->get->folder) {
			$folder = $input->get->text('folder');
			$filename = $input->get->text('document');
			$document_management->move_document($folder, $filename);

			if ($document_management->is_filewebaccessible($filename)) {
				$session->redirect($config->url_webdocs.$filename);
			}
		}

		$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);


		// JSON FORMAT
		// $module_json = $modules->get('JsonDataFiles');
		// $json = $module_json->get_file(session_id(), $page->jsoncode);
		// if ($module_json->file_exists(session_id(), $page->jsoncode)) {
		// 	$session->documentstry = 0;
		// 	$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID]);
		// 	$page->body .= $config->twig->render('items/ii/documents/documents.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json]);
		// } else {
		// 	if ($session->documentstry > 3) {
		// 		$page->headline = $page->title = "Documents File could not be loaded";
		// 		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
		// 	} else {
		// 		$session->documentstry++;
		// 		$session->redirect($page->get_itemdocumentsURL($itemID));
		// 	}
		// }
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
