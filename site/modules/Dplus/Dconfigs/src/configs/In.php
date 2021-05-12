<?php namespace Dplus\Configs;

use ConfigInQuery, ConfigIn;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigIn
 * Class for getting IN config
 */
class In extends AbstractConfig {
	const MODEL = 'ConfigIn';
}
