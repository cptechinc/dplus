<?php
use Dplus\DocManagement\Viewer as DocViewer;

$docView = DocViewer::getInstance();

$config->js('api', [
	'urls' => [
		'mdm' => [
			'docvwr' => [
				'copy' => $page->jsonApiUrl('mdm/docvwr/copy')
			]
		]
	]
]);

$config->js('config', [
	'urls' => [
		'docvwr' => $docView->url('')
	]
]);
