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

// Link to SMF forum as this is only for logged in members
// Show all errors:
error_reporting(E_ALL);
define('SERVER_RUNNING','./data/server.run');
if(file_exists(SERVER_RUNNING)) { //someone might be in chat
    exec('php ./server.php');  //Make sure server is going to stay running for a while
    define ('MBC',1);   //defined so we can control access to some of the files.
    require_once('./client.php');

    $c = new ChatServer();

    echo $c->cmd('count');
} else {
    echo '0';
}
