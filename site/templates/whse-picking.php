<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('WarehouseInventoryConfig');
	$config_picking = $modules->get('WarehousePickingConfig');
	$template = '';

	switch ($config_picking->picking_method) {
		case 'guided':
			$template = 'whse-picking-guided';
			break;
		case 'unguided';
			$template = 'whse-picking-unguided';
			break;
	}
	include("./$template.php");
