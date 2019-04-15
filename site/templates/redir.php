<?php
	echo $page->redir;
	$redirect = rtrim($page->redir_file, '.php') . ".php";
	include($config->paths->templates."redir/$redirect");
