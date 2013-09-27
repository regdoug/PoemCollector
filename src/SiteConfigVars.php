<?php
/**
 * SiteConfigVars.php is provided by the ITS web environment
 *
 * This is just a dummy for testing.  To use this file, first
 * replace [[YOUR_PASSWORD_HERE]] with your password.  Make
 * sure that your database user is named "w-hon" and "w-hon"
 * has permissions on a database "w_hon"
 */

function getConfigValue($key){
	switch($key){
		case 'dbHost_w_hon':return 'localhost';
		case 'dbPass_w_hon':return '[[YOUR_PASSWORD_HERE]]';
	}
}
