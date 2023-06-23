<?php namespace Dplus\Configs;

use ConfigSoFreightQuery, ConfigSoFreight;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * Sofrt
 * Class for getting SOFRT config
 */
class Sofrt extends AbstractConfig {
	const MODEL = 'ConfigSoFreight';
}
