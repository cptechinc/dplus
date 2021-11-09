<?php namespace Controllers\Mpm\Pmmain;
// External Libraries, classes
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use PrWorkCenter;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes\Mpm\Dcm as DcmManager;
// Mvc Controllers
use Controllers\Mpm\Base as BaseMpm;

class Base extends BaseMpm {
	const DPLUSPERMISSION = 'pmmain';
	const TITLE_MENU = 'Maintenance';
	const SHOWONPAGE = 10;
}
