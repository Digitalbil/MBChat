<?php
if(!(isset($_GET['user']) && isset($_GET['password']) && isset($_GET['lid']) && isset($_GET['rid'])))
	die('Poll-Hacking attempt - wrong parameters');
$uid = $_GET['user'];

if ($_GET['password'] != sha1("Key".$uid))
	die('Hacking attempt got: '.$_GET['password'].' expected: '.sha1("Key".$uid));
$lid = $_GET['lid'];
$rid = $_GET['rid'];
define ('MBC',1);
require_once('db.php');
$sql = 'SELECT lid, UNIX_TIMESTAMP(time) AS time, type, rid, log.uid AS uid , name, role, text  FROM log';
$sql .= ' LEFT JOIN participant ON participant.wid = rid WHERE lid > '.dbMakeSafe($lid).' AND ( participant.uid = '.dbMakeSafe($uid);
$sql .= 'OR rid = '.dbMakeSafe($rid).' OR type IN ("LO","LT","PE"';
if ($rid == 0 ) {
	$sql .= ',"RE", "RX" ';	//In entrance hall,also interested in entering or leaving another room
}
$sql .= ')) ORDER BY lid;';
$result = dbQuery($sql);
$messages = array();
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
		$item['time'] = $row['time'];
		$item['message'] = $row['text'];
		$messages[]= $item;
	}		
};
mysql_free_result($result);
echo '{"messages":'.json_encode($messages).'}';
?>