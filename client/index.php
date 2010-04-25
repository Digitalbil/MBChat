<?php
/*
 	Copyright (c) 2009, 2010 Alan Chandler
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



define ('MBC',1);   //defined so we can control access to some of the files.
include_once('./client.inc');

$c = new ChatServer();
$c->start_server(SERVER_KEY); //Start Server if not already going.
$go = $c->getParam('exit_location');

header("location: ".$go);
?>
