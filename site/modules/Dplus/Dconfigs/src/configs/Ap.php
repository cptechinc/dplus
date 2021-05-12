<?php namespace Dplus\Configs;

use ConfigApQuery, ConfigAp;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigAp
 * Class for getting AP config
 */
class Ap extends AbstractConfig {
	const MODEL = 'ConfigAp';
}
