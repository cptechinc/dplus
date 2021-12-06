<?php
use Dplus\DocManagement\Viewer as DocViewer;

$docView = DocViewer::getInstance();

$config->js('api', [
	'urls' => [
		'mdm' => [
			'docs' => [
				'copier' => $page->jsonApiUrl('mdm/docs/copier')
			]
		]
	]
]);

$config->js('config', [
	'urls' => [
		'docvwr' => $docView->url('')
	]
]);
