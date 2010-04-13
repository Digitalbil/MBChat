<?php
/*
 	Copyright (c) 2009,2010 Alan Chandler
    This file is part of MBChat.

    MBChat is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBChat is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBChat (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.
*/

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: -1"); // Date in the past
if(!(isset($_POST['user']) && isset($_POST['password']) ))
	die('Poll-Hacking attempt - wrong parameters');
$uid = $_POST['user'];

if ($_POST['password'] != sha1("Key".$uid))
	die('Hacking attempt got: '.$_POST['password'].' expected: '.sha1("Key".$uid));



$readpipe=fopen("./data/msg".$uid,'r');
while (!feof($readpipe)) {
    $response .= fread($readpipe,8192);
}
fclose($readpipe);

if (strlen($response) > 0 ) {
    $messages =  explode('>',$response);
    array_pop($messages); //the last will always be null

    $lid = substr(strstr(strstr($messages[0],":"),",",TRUE),1);      
    //we need to make sure that the first lid of messages is not greater that we were expecting
    if(isset($_POST['lid']) && $lid > $_POST['lid']) {
    
        define ('MBC',1);   //defined so we can control access to some of the files.
        require_once('db.php');
        foreach(dbQuery("SELECT * FROM log WHERE lid >= ".dbMakeSafe($_POST['lid']).
                    " AND lid < ".dbMakeSafe($lid)." ORDER BY lid DESC;") as $row) {
            $message = '<{"lid":'.$row['lid'].',"user" :{"uid":'.$row['uid'].',"name":"'.$row['name'].'","role":"';
            $message .= $row['role'].'"},"type":"'.$row['type'].'","rid":'.$row['rid'].',"message":"'.$row['text'].'","time":'.$row['time'].'}';
            array_unshift($messages,$message);
        }   
        $lid = substr(strstr(strstr($messages[0],":"),",",TRUE),1);      
    }

    echo '{"lastlid": '.$lid.' ,"messages":[' ;
    $i=0;
    foreach($messages as $message) {
        if($i != 0) {
            echo ",";
        }
        $i++;
        echo substr($message,1) ;
    }
    echo ']}';
} else {
 echo '{"status":"time"}';
}
?>
