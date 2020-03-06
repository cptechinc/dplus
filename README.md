# Welcome to Dplus Online

## Table of Contents

1. [About](#about-dplus-online)
2. [Updates](#updates)

## About Dplus Online
Dplus Online is a online PHP-based Interface to Distribution Plus


It Provides Web Portals to the Distribution Plus Functions
* Warehouse Management
* Orders Management
* Quotes Management
* Customer Management


## updates
Below is the code that can be used to update the page structure and install modules that are needed.
```
	$module = $modules->get('DplusPwPages');
	$module->update();

	$module = $modules->get('Dpages');
	$module->update();

	$info = $module->getModuleInfo();
	$dpages = $info['installs'];

	foreach ($dpages as $dpage) {
		if (!$modules->isInstalled()) {
			$modules->install($dpage);
		}
		$module = $modules->get($dpage);
		$module->update();
	}

	$module = $modules->get('Xrefs');
	$module->update();

	$module = $modules->get('Filters');
	$module->update();
```
