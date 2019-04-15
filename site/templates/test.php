<?php 
	use Map\LogpermTableMap;
	$q = LogpermQuery::create();
	$con = Propel\Runtime\Propel::getWriteConnection(LogpermTableMap::DATABASE_NAME);
	$logperm = $q->findOneBySessionid(session_id());
	//echo $logperm->loginid;
