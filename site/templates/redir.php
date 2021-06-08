<?php
	$redirect = rtrim($page->redir_file, '.php') . ".php";
	include_once($config->paths->templates."redir/$redirect");
