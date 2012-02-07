<?php
/**
 * init.php
 * Initializes all cron scripts.
 * Connects to database and ensures safe execution.
 */
//Kill if we need to.
if(!CRONSCRIPT) die;

$db = array(
	'host' => 'mydellminisql.dc.fubra.net',
	'login' => 'umpc',
	'password' => 'UyuD5a2nbTCEC6FG',
	'database' => 'buyersguide'
);
$link = mysql_connect($db['host'], $db['login'], $db['password']);
mysql_select_db($db['database'], $link);
unset($db);

//Basic error function to email me.
function errorme($subject,$reason) {
	mail('matt+error@umpcmedia.com',$subject,$reason);
	die('Something went wrong. matt@umpcmedia.com has been informed.');
}