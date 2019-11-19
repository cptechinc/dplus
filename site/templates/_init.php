<?php
	use Map\SalesOrderTableMap;
	use Map\QnoteTableMap;

/**
 * Initialization file for template files
 *
 * This file is automatically included as a result of $config->prependTemplateFile
 * option specified in your /site/config.php.
 *
 * You can initialize anything you want to here. In the case of this beginner profile,
 * we are using it just to include another file with shared functions.
 *
 */

include_once("./_func.php"); // include our shared functions

// CHECK DATABASE CONNECTIONS
if ($page->id != $config->errorpage_dplusdb) {
	if (empty(wire('dplusdata')) || empty(wire('dplusodb'))) {
		$session->redirect($pages->get($config->errorpage_dplusdb)->url, $http301 = false);
	}

	$serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
	$serviceContainer->checkVersion('2.0.0-dev');

	$db_modules = array(
		'dplusdata' => array(
			'module'          => 'DplusConnectDatabase',
			'connection-name' => 'default'
		),
		'dpluso' => array(
			'module'          => 'DplusOnlineDatabase',
			'connection-name' => 'dplusodb'
		)
	);

	foreach ($db_modules as $key => $connection) {
		$module = $modules->get($connection['module']);
		$manager = $module->get_propel_connection();
		$serviceContainer->setAdapterClass($connection['connection-name'], 'mysql');
		$serviceContainer->setConnectionManager($connection['connection-name'], $manager);
	}

	$serviceContainer->setDefaultDatasource('default');

	$con = Propel\Runtime\Propel::getWriteConnection(SalesOrderTableMap::DATABASE_NAME);
	$con->useDebug(true);

	$dpluso = Propel\Runtime\Propel::getWriteConnection(QnoteTableMap::DATABASE_NAME);
	$dpluso->useDebug(true);

	$templates_nosignin = array('login', 'redir');

	if ($input->get->pdf || $input->get->print) {

	} elseif (!in_array($page->template, $templates_nosignin) && LogpermQuery::create()->is_loggedin(session_id()) == false) {
		$session->redirect($pages->get('template=login')->url, $http301 = false);
	}

	$user->setup(session_id());
}

// ADD JS AND CSS
$config->styles->append(hash_templatefile('styles/bootstrap-grid.min.css'));
$config->styles->append(hash_templatefile('styles/theme.css'));
$config->styles->append('//fonts.googleapis.com/css?family=Lusitana:400,700|Quattrocento:400,700');
$config->styles->append('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
$config->styles->append(hash_templatefile('styles/lib/fuelux.css'));
$config->styles->append(hash_templatefile('styles/lib/sweetalert.css'));
$config->styles->append(hash_templatefile('styles/main.css'));


$config->scripts->append(hash_templatefile('scripts/lib/jquery.js'));
$config->scripts->append(hash_templatefile('scripts/popper.js'));
$config->scripts->append(hash_templatefile('scripts/bootstrap.min.js'));
$config->scripts->append(hash_templatefile('scripts/lib/fuelux.js'));
$config->scripts->append(hash_templatefile('scripts/lib/sweetalert.js'));
$config->scripts->append(hash_templatefile('scripts/lib/moment.js'));
$config->scripts->append(hash_templatefile('scripts/lib/bootstrap-notify.js'));
$config->scripts->append(hash_templatefile('scripts/uri.js'));
$config->scripts->append(hash_templatefile('scripts/lib/sweetalert.js'));
$config->scripts->append(hash_templatefile('scripts/classes.js'));
$config->scripts->append(hash_templatefile('scripts/main.js'));


// BUILD AND INSTATIATE CLASSES
$page->fullURL = new Purl\Url($page->httpUrl);
$page->fullURL->path = '';
if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
	$page->fullURL->join($_SERVER['REQUEST_URI']);
}

// SET CONFIG PROPERTIES
if ($input->get->modal) {
	$config->modal = true;
}

if ($input->get->json) {
	$config->json = true;
}

if ($input->get->print) {
	$page->print = true;
}

if ($input->get->pdf) {
	$page->pdf = true;
}

$appconfig = $pages->get('/config/app/');
$siteconfig = $pages->get('/config/');
$config->customer = $pages->get('/config/customer/');

$session->sessionid = session_id();

$loader = new Twig_Loader_Filesystem($config->paths->templates.'twig/');
$config->twig = new Twig_Environment($loader, [
	'cache' => $config->paths->templates.'twig/cache/',
	'auto_reload' => true,
	'debug' => true
]);

$config->twig->addExtension(new Twig\Extension\DebugExtension());
include($config->paths->templates."/twig/util/functions.php");

if ($page->fullURL->query->__toString() != '') {
	$page->title_previous = $page->title;
}

$page->show_breadcrumbs = true;
