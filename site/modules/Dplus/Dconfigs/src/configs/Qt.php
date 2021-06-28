<?php namespace Dplus\Configs;

use ConfigQtQuery, ConfigQt;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigSo
 * Class for getting CI config
 */
class Qt extends AbstractConfig {
	const MODEL = 'ConfigQt';
}
