<?php namespace Dplus\Configs;

use ConfigPoQuery, ConfigPo;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigPo
 * Class for getting Purchase Orders config
 */
class Po extends AbstractConfig {
	const MODEL = 'ConfigPo';
}
