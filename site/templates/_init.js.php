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
	],
	'ajax' => [
		'urls' => [
			'lookup' => $page->searchLookupUrl(''),
			'json'   => $page->jsonApiUrl(''),
			'locker' => [
				'check' => $page->jsonApiUrl('util/recordlocker/check/', ['function' => '', 'key' => '']),
				'lock' => $page->jsonApiUrl('util/recordlocker/lock/', ['function' => '', 'key' => '']),
				'delete' => $page->jsonApiUrl('util/recordlocker/delete/', ['function' => '', 'key' => '']),
			]
		]
	]
]);

$agent = new Jenssegers\Agent\Agent();
$config->js('agent', [
	'browser' => strtolower($agent->browser())
]);

$config->js('user', [
	'id' => $user->loginid,
	'dplus' => [
		'whseid' => $user->whseid
	]
]);

$config->js('vars', []);
