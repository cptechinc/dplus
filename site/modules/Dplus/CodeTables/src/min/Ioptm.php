<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use SysopOptionalCodeQuery, SysopOptionalCode as Model;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Codes
use Dplus\Codes\Msa\SysopOptionalCode;

/**
 * Class that handles the CRUD of the Sysop code table
 */
class Ioptm extends SysopOptionalCode {
	const RESPONSE_TEMPLATE     = '{sysop} Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ioptm';
	const DPLUS_TABLE           = '';
	const SYSTEM = 'IN';
}
