<?php namespace Dplus\Configs;

use ConfigSalesOrderQuery, ConfigSalesOrder;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigSo
 * Class for getting CI config
 */
class So extends AbstractConfig {
	const MODEL = 'ConfigSalesOrder';
}
