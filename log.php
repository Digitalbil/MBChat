<?php
if(!(isset($_GET['user']) && isset($_GET['password']) && isset($_GET['rid']) && isset($_GET['start'])&& isset($_GET['end'])))
	die('Log - Hacking attempt - wrong parameters');
$uid = $_GET['user'];
if ($_GET['password'] != sha1("Key".$uid))
	die('Log - Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));
$rid = $_GET['rid'];

define ('MBC',1);   //defined so we can control access to some of the files.
require_once('db.php');


$sql = 'SELECT lid, UNIX_TIMESTAMP(time) AS utime, type, rid, uid , name, role, text  FROM log';
$sql .= ' WHERE UNIX_TIMESTAMP(time) > '.dbMakeSafe($_GET['start']).' AND UNIX_TIMESTAMP(time) < '.dbMakeSafe($_GET['end']).' AND rid ';
if ($rid == 0) {
	$sql .= '> 99 ORDER BY rid,lid ;';
} else {
	$sql .= '= '.dbMakeSafe($rid).' ORDER BY lid ;';
}
$result = dbQuery($sql);
echo '{"messages" :[' ;
$i = 0 ;
if(mysql_num_rows($result) != 0) {
	while($row=mysql_fetch_assoc($result)) {
		$user = array();
		$item = array();
		$item['lid'] = $row['lid'];
		$item['type'] = $row['type'];
		$item['rid'] = $row['rid'];
		$user['uid'] = $row['uid'];
		$user['name'] = $row['name'];
		$user['role'] = $row['role'];
		$item['user'] = $user;
		$item['time'] = $row['utime'];
		$item['message'] = $row['text'];
		if ($i != 0) {
			echo ',';
		}
		$i++ ;
		echo json_encode($item) ;
	}		
};
mysql_free_result($result);

echo ']}';
?>