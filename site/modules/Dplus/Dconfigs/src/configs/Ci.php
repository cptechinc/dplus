<?php namespace Dplus\Configs;

use ConfigCiQuery, ConfigCi;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigCi
 * Class for getting CI config
 */
class Ci extends AbstractConfig {
	const MODEL = 'ConfigCi';
}
