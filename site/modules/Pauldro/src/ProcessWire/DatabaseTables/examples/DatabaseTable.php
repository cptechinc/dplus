<?php
	# Examples  for constats for DatabaseTable based classes
	const COLUMNS = [
		'username'		=> ['VARCHAR(32)', 'NOT NULL'],
		'token' 		=> ['VARCHAR(500)', 'NOT NULL'],
		'refreshtoken'	=> ['VARCHAR(500)', 'NOT NULL'],
		'expires'		=> ['VARCHAR(12)', 'NOT NULL'],
		'modified'		=> ['TIMESTAMP', 'NOT NULL'],
	];
	const PRIMARYKEY = ['username'];
	const MODEL_CLASS = '\\ProcessWire\\WireData';