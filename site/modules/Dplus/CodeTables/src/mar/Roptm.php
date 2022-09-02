<?php namespace Dplus\Codes\Mar;
// Dplus Codes
use Dplus\Codes\Msa\SysopOptionalCode;

/**
 * Class that handles the CRUD of the Sysop code table
 */
class Roptm extends SysopOptionalCode {
	const RESPONSE_TEMPLATE     = '{sysop} Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'roptm';
	const DPLUS_TABLE           = '';
	const SYSTEM = 'AR';
}
