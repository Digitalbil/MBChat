<?php
if(!(isset($_GET['user']) && isset($_GET['password']) && isset($_GET['wid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_GET['user'];
if ($_GET['password'] != sha1("Key".$uid))
	die('Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));
$wid = $_GET['wid'];
define ('MBC',1);   //defined so we can control access to some of the files.
require_once('db.php');
dbQuery('START TRANSACTION;');

$result = dbQuery('SELECT users.uid, name, role, wid FROM users JOIN particpant ON users.uid = particpant.uid 
		WHERE users.uid = '.dbMakeSafe($uid).' AND wid = '.dbMakeSafe($wid).' ;');
if(mysql_num_rows($result) == 0) {
	dbQuery('ROLLBACK;');
	die('Leave Whisper - Invalid Parameters');
}
$row = mysql_fetch_assoc($result);
mysql_free_result($result);
dbQuery('DELETE FROM participant WHERE uid = '.dbmakeSafe($uid).' AND wid = '.dbMakeSafe($wid).' ;');
dbQuery('INSERT INTO log (uid, name, role, type, rid) VALUES ('.
	dbMakeSafe($uid).','.dbMakeSafe($row['name']).','.dbMakeSafe($row['role']).
	', "WL" ,'.dbMakeSafe($wid).');');



dbQuery('UPDATE users SET time = NOW() WHERE uid = '.dbMakeSafe($uid).';');

dbQuery('COMMIT ;');


echo '{ "Status" : "OK"}';

?>