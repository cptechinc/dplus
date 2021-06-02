<?php namespace Dplus\Configs;

use ConfigKtQuery, ConfigKt;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigKt
 * Class for getting AP config
 */
class Kt extends AbstractConfig {
	const MODEL = 'ConfigKt';
	use ConfigTraits;
}
