<?php
$routes = [
	['OPTIONS', 'test', ['GET']], // this is needed for CORS Requests
	['GET', 'test', Example::class, 'test'],
];
