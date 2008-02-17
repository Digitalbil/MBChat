<?php
/* A new version of chat
	Copyright (c) 2008 Alan Chandler
	Licenced under the GPL
*/
// Link to SMF forum as this is only for logged in members
// Show all errors:
error_reporting(E_ALL);
// Path to the chat directory:

define('MBCHAT_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');

require_once(MBCHAT_PATH.'../forum/SSI.php');
//If not logged in to the forum, not allowed any further so redirect to page to say so
if($user_info['is_guest']) {
	header( 'Location: http://mb.home/static/Chat.htm' ) ;
};

// SMF membergroup IDs for the groups that we have used to define characteristics which control Chat Group
define('SMF_CHAT_BABY',		10);
define('SMF_CHAT_LEAD',		9);
define('SMF_CHAT_MODERATOR',	14);
define('SMF_CHAT_MELINDA',	13);
define('SMF_CHAT_HONORARY',	20);
define('SMF_CHAT_SPECIAL',	19);

// These need to match the roomID in the database
define('MBCHAT_MEMBERS_LOUNGE',	1);
define('MBCHAT_BLUE_ROOM',	2);
define('MBCHAT_GREEN_ROOM',	3);
define('MBCHAT_VAMP_CLUB',	4);
define('MBCHAT_AUDITORIUM',	5);


define ('MBC',1);   //defined so we can control access to some of the files.

$groups =& $user_info['groups'];
require('user.php');
$user = new User($ID_MEMBER,           //userID
		$user_info['name'],    //name
		(in_array(SMF_CHAT_LEAD, $groups))? (($user_info['is_admin'])? 'A' : 'L') :   // which role 
			((in_array(SMF_CHAT_BABY, $groups))? 'B' :(
			(in_array(SMF_CHAT_MODERATOR, $groups))? 'M' :(
			(in_array(SMF_CHAT_MELINDA, $groups))?'H' :(
			(in_array(SMF_CHAT_HONORARY, $groups))? 'G' :(
			(in_array(SMF_CHAT_SPECIAL, $groups))?'S' : 'R'))))));
		
//chat class
require('mbchat.php');
//Create Chat object that will provide us with all the info needed
$chat = new MBChat($user);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Community Rooms</title>
	<link rel="stylesheet" type="text/css" href="/static/css/chat.css" title="mbstyle"/>
	<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="/static/css/chat-ie.css"/>
	<![endif]-->
	<style type="text/css">
		#entranceHall {
			position:absolute;
			top:50px;
			left:70px;
			width:600px;
			text-align:center;
			background-color: #4A7DB5;
		}
		#entranceHall h3 {
			font-size:1.5em;
			font-weight: bold;
			color:white;
			margin:0px;
		}

		.functions {
			position: relative;
			padding: 10px 0;
			display: block;
		}

		.functions li {
			display:block;
			float: left;
		}

		.function {
			display: block;
			position:relative;
			cursor: pointer;
			height: 85px;
			width: 105px;
			padding: 5px;
			border-right: 5px solid #4A7DB5;
			text-decoration:none;
			text-align:left;
		}

		.function>span {
			display: none;
		}
	

		.member span {
			display:inline;	
			color:white;
			font-size:2em;
			font-weight:bold;
		}

		#members-lounge {
			background: #78ba91 url(/static/images/members-lounge.gif) no-repeat;
		}

		#blue-room {
			background: #7389ae url(/static/images/blue-room.gif) no-repeat;
		}

		#green-room {
			background: #c17878 url(/static/images/green-room.gif) no-repeat;
		}

		#vamp-club {
			background: #c17878 url(/static/images/vamp-club.gif) no-repeat;
		}

		#auditorium {
			background: #a87aad url(/static/images/auditorium.gif) no-repeat;
		}

		.member {
			background: #c17878 url(/static/images/member-room.gif) no-repeat;
		}

		#forum {
			background:white url(/static/images/forum.gif) no-repeat;
		}

		#user-settings {
			background:white url(/static/images/user-settings.gif) no-repeat;
		}

		#create-room {
			background:white url(/static/images/create-room.gif) no-repeat;
		}

		#user-settings-input {
			margin-left:115px;
			font-size:.75em;
			width:100px;
			color:red;
		}
		.whoonline {
			display:none;
			position:absolute;
			padding:0px;
			margin:0px;
			top:-20px;
			right:-110px;
			overflow:hidden;
			background-color:white;
			width:0px;
			opacity:0.9;
			z-index:2;
		} 
		.whoonline h4 {
			background-color:#555555;
			color:white;
			text-align:center;
			margin:0px;
			padding:2px;
		}
		.onlinelist {
			padding:5px;
			color:black;
			overflow:auto;
		}
		

		
	</style>
	<script src="/static/scripts/mootools.js" type="text/javascript" charset="UTF-8"></script>
	<script src="/static/scripts/soundmanager2.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>
<script type="text/javascript">
	<!--

window.addEvent('domready', function() {
	var functiongroups = $$('.functions');
	var myTransition = new Fx.Transition(Fx.Transitions.Bounce, 6);
	functiongroups.each( function (functiongroup,i) {
		var functions = functiongroup.getElements('.function');
		var fx = new Fx.Elements(functions, {wait: false, duration: 500, transition: myTransition.easeOut });
		functions.each( function(functionitem, i){
			var makeVisible = function(e) {
				functionitem.setStyle('overflow' , 'visible');					
			};
			var online = functionitem.getElements('.whoonline');
			if (online.length > 0 ) {
				online = online[0];
			} else {
				online = false;
			}
			functionitem.addEvent('mouseenter', function(e){
				var obj = {};
				obj[i] = {
					'width': [functionitem.getStyle('width').toInt(), 219],
				};
				functions.each(function(other, j){
					if (other != functionitem){
						var w = other.getStyle('width').toInt();
						if (w != 67) obj[j] = {'width': [w, 67]};
					}
				});
				fx.addEvent('onComplete',makeVisible );
				fx.start(obj);
				if (online) {
					online.tween('width', 0,120);
					online.setStyle('display','block');
					MBChat.updateOnlineList(online);
				}
			});
			functionitem.addEvent('mouseleave', function(e){
				functionitem.setStyle('overflow' , 'hidden');
				var obj = {};
				functions.each(function(other, j){
					obj[j] = {'width': [other.getStyle('width').toInt(), 105]};
				});
				fx.removeEvent('onComplete',makeVisible);
				fx.start(obj);
				online.setStyle('display','none');
			});
		});
	});	
});

soundManager.onload = function () {
	soundManager.createSound({
		id : 'entrance',
		url : '/static/sounds/mfv.mp3',
		autoLoad : true ,
		autoPlay : true ,
		onfinish : function () {
			soundManager.play('entrance');
		},
		volume : 10
	});
	soundManager.play('entrance');
};
var MBChat = {
	userSetting: function(element,value) {
	},
	updateOnlineList : function (list) {
	}
}
	// -->
</script>

<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" >
<tbody>
	<tr>
	<td align="left" width="30" class="topbg_l" height="70">&nbsp;</td>
	<td align="left" colspan="2" class="topbg_r" valign="top"><a href="/" alt="Main Site Home Page">
		<img  style="margin-top: 24px;" src="/static/images/mb-logo-community.gif" alt="Melinda's Backups Community" border="0" /></a>	
		</td>
	<td align="right" width="400" class="topbg" valign="top">
	<span style="font-family: tahoma, sans-serif; margin-left: 5px;">Melinda's Backups Community</span>
	</td>
		<td align="right" width="25" class="topbg_r2" valign="top">
		<div id="roomNameContainer">
			<h1>Entrance Hall</h1>
		</div>
		<!-- blank -->
		</td>
	</tr>  </tbody>
</table>

<div id="content">
<div id="entranceHall">
	<div  class="functions">
	<h3>Main Rooms</h3>
		<ul>
			<li><a id="members-lounge" class="function" href=<?php
				echo $chat->generateRoomURL(MBCHAT_MEMBERS_LOUNGE); ?>>
				<span>Members Lounge</span>
				<div class="whoonline"><h4>Users In Room</h4><div class="onlinelist"></div></div></a></li>
			<li><a id="<?php echo $chat->getAdultOrBabyRoom(); ?>" class="function" href=<?php
				echo $chat->generateRoomURL(($chat->getAdultOrBabyRoom() == 'green-room')? 
				MBCHAT_GREEN_ROOM:MBCHAT_BLUE_ROOM); ?>>
				<span>Blue or Green Room</span>
				<div class="whoonline"><h4>Users In Room</h4><div class="onlinelist"></div></div></a></li>
			<li><a id="vamp-club" class="function" href=<?php
				echo $chat->generateRoomURL(MBCHAT_VAMP_CLUB); ?>>
				<span>Vamp Club</span>
				<div class="whoonline"><h4>Users In Room</h4><div class="onlinelist"></div></div></a></li>
			<li><a id="auditorium" class="function" href=<?php
				echo $chat->generateRoomURL(MBCHAT_AUDITORIUM);?>>
				<span>Auditorium</span>
				<div class="whoonline"><h4>Users In Room</h4><div class="onlinelist"></div></div></a></li>
		</ul>
		<div style="clear:both"></div>
	</div>
	<?php 
$rooms = Array();
$rooms = $chat->getRoomNames();
if(count($rooms) > 0 ) {		
		$i = 0;
		foreach ($rooms as $roomId => $roomName ) {
			if( ($i % 4) == 0 ) {
		?><div class="functions"> 
	<h3>Member Rooms</h3>
		<ul>
		<?php	};
			$i++; ?>
	<li><a class="function member" href=<?php
				echo $chat->generateRoomURL($roomId);?>>
			<span><?php echo $roomName ; ?></span>
				<div class="whoonline"><h4>Users In Room</h4><div class="onlinelist"></div></div></a></li>
		<?php  if( ($i % 4) == 0 ) {
				?></ul>
		<div style="clear:both"></div>
	</div>
<?php 
			};
		};
		 //If ended loop and hadn't just comleted div we will have to do it here
 		if( ($i % 4) != 0 ) { 
			?>
</ul>
		<div style="clear:both"></div>
	</div>
<?php	}; 
};
?>
	<div class="functions">
	<h3>Other Functions</h3>
		<ul>
			<li><a id="forum" class="function" href="/forum"><span>Return to Forum</span></a></li>
			<li><a id="user-settings" class="function" href="#" onclick="return false">
				<div id="user-settings-input">
					<input type="checkbox" name="sounds" id="sounds-field" checked=<?php 
						echo $chat->userSoundSetting(); 
						?> onclick="MBChat.userSetting('sounds', this.checked);"/>
					<label for="sounds-field">Enable Sounds</label><br/>
					<input type="text" name="gap" size="1" id="gap-delay"
						onchange="MB.Chat.userSetting('gap',this.value);" value=<?php 
						echo $chat->userSoundDelay(); ?> />
					<label for="gap-delay">Gap in Minutes for new message warning</label>
				</div></a></li>
<?php	if ($chat->mayCreateRooms()) {
?>			<li><a id="create-room" class="function" href=<?php echo $chat->createRoomURL(); ?>>
				<span>Create Room</span></a></li>
<?php }; 
?>		</ul>
		<div style="clear:both"></div>

	</div>
</div>


</div>
</body>

</html>