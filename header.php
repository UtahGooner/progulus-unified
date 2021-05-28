<? 
require_once("phpbb_login_short.php"); 
$station = (isset($station) ? $station : "Progulus Radio");
$pageName = (isset($pageName) ? $pageName : "Untitled");
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
    <title><?="{$station} - {$pageName}";?></title>

    <meta name="keywords" content="internet radio station metal heavy metal progressive metal progressive rock art rock indie rock indie metal avante garde beauty and beast B&amp;B death metal">
    <meta name="description" content="Internet Radio Station">
    <meta name="robots" content="ALL">
    <meta name="rating" content="General">
    <meta name="copyright" content="2005-2007 Progulus Radio">
    <meta name="author" content="Progulus Radio">
    <meta name="language" content="en">

	<link rel="stylesheet" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
	<!-- <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css"> -->
	<link rel="stylesheet" href="css/progulus-styles.css" type="text/css">
	<link rel="stylesheet" href="css/default.css" type="text/css">
	<link rel="stylesheet" href="css/menu.css" type="text/css">
	<link rel="stylesheet" href="css/callout.css" type="text/css">
	<link rel="stylesheet" href="css/messagebox.css" type="text/css">
	<link rel="stylesheet" href="css/main.css" type="text/css">
	<link rel="stylesheet" href="css/rating.css" type="text/css">
	<!--[if lt IE 7]>
    <link rel="stylesheet" href="css/ie6fix.css" type="text/css">
    <![endif]-->

	<script type="text/javascript" src="http://yui.yahooapis.com/2.5.2/build/yahoo-dom-event/yahoo-dom-event.js"></script> 
	<script type="text/javascript" src="http://yui.yahooapis.com/2.5.2/build/connection/connection-min.js"></script> 
	<script type="text/javascript" src="http://yui.yahooapis.com/2.5.2/build/json/json-min.js"></script>

	<script type="text/javascript" src="javascript/reaperlib.js"></script>
	<script type="text/javascript" src="javascript/header.js"></script>
	<script type="text/javascript" src="javascript/yuiheader.js"></script>

	<script type="text/javascript" src="javascript/messagebox.js"></script>
	<script type="text/javascript" src="/rprweb/javascript/stats.js"></script>
	<script type="text/javascript" src="/rprweb/javascript/request.js"></script>
</head>
<body onload="init();">

<div id="page"> <? // This div is closed in footer.php ?>
	<div id="doc2" class="yui-t3">
	<div id="hd">
		<div id="header">
			<?php
				if(date("md") == "0919") {
					echo "<div id=\"tlapd\">&nbsp;</div>\n";
				}
			?>
			<div id="beta" class="MessageBox">
				<div id="beta_title" class="titleBar" onmousedown="// mesageBox.grap(this.parentNode);">* Progulus V2</div>
				<div id="beta_collapse" class="collapseButton" onmousedown="messageBox.collapse(this.parentNode);">-</div>
				<div id="beta_close" class="closeButton" onmousedown="messageBox.close(this.parentNode);">X</div>
				<div id="beta_message" class="message">
				<?php
				$userid = (int) (isset($userdata['user_id']) ? $userdata['user_id'] : -1);
				if($userid <= 0) {
				?>
					<form action="/forums/login.php" method="post" id="login_form">
						<label for="username">Username</label><input type="text" name="username"  id="username"><br>
						<label for="password">Password</label><input type="password" name="password" id="password"><br>
						<input type="hidden" name="redirect" value="<?php echo (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ""); ?>">
						<input type="hidden" name="login" value="login">
						<input type="submit" name="login" value="Login" id="login_button"> 
					</form>
				<?php
				}
				@include("message.inc.php"); 
				?>
				</div>
			</div>
			<script type="text/javascript">
			</script>
		
		</div>
		<!--
		<div id="menu">
			<ul>
				<li title="What's currently Playing on Progulus">Now Playing</li>
				<li title="Search the Library or Make a request">Request</li>
				<li title="Forums, Comments, Ratings">Forums</li>
				<li title="Top-ten, New Additions">Song Lists</li>
				<li title="Calendar of Shows and Events at Progulus">Schedule</li>
				<li title="History, Contact Info, Links">About</li>
				<li>Gallery</li>
			</ul>
		</div>
		  -->
		<div id="menu" class="clearfix">
<?
if(!defined("LINK_TGT_SELF")) define("LINK_TGT_SELF", "_self");
if(!defined("LINK_TGT_BLANK")) define("LINK_TGT_BLANK", "_blank");

function putMenuItem( $text, $url, $imgsrc, $row, $target = LINK_TGT_SELF )
{
	if($target != LINK_TGT_SELF && $target != LINK_TGT_BLANK) $target = LINK_TGT_SELF;
	$rowstyle = (($row & 0x1) == 0x1)? "odd" : "even";
	echo "<li title=\"{$text}\" class=\"{$rowstyle}\">";
 
	if (isset($use_icons_for_links) && $use_icons_for_links == true) {
		echo "<a href=\"{$url}\" target=\"{$target}\"><img src=\"{$imgsrc}\"></a>";
	}

    echo "<a href=\"{$url}\" target=\"{$target}\">{$text}</a>";
    echo "</li>";
}

$i = 0;
 ?>		
	<ul>
	<li class="plain">
		<a href="http://www.live365.com/stations/progulus?play"><img src="http://www.live365.com/images/listen-me-rad-reg.gif" alt="play"></a>
		<a href="http://www.live365.com/stations/progulus" id="listen_link" target="_blank">Listen Now</a>
		<small>(<a href="http://www.progulus.com/forums/viewtopic.php?t=29" target="_blank">Tips</a>)</small>
	</li>
	<li class="spacer"></li>
<?

if ($have_now_playing_link) {
    putMenuItem( "Now Playing", "playing.php", "images/now-playing.gif", $i++ );
}
if(!defined("BACKUP_MODE") || BACKUP_MODE != 1) {
	putMenuItem( "Request", "search3.php", "images/playlist-requests.gif", $i++ );
}
if ( $have_playlist_schedule_link) {
    putMenuItem( "Schedule", "schedule.php", "images/schedule.gif", $i++ );
}

if ( $have_forums_link) {
	putMenuItem( "Forums", $forums_link_url, "images/forums.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_weather_link) {
	putMenuItem( "Weather", "weather.php", "images/weather.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_show_history_link) {
    putMenuItem( "Show History", "show-history.php", "images/show-history.gif", $i++, LINK_TGT_BLANK );
}  

if (false && $have_browse_pools_link) {
    putMenuItem( "Browse Pools", "browse-pools.php", "images/browse-pools.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_new_additions_link) {
    putMenuItem( "New Additions", "new-additions.php", "images/new.gif", $i++, LINK_TGT_BLANK );
}  

if (false && $have_top_users_list_link) {
    putMenuItem( "Top Users", "top_users.php", "images/top_users.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_my_pal_scripts_link) {
    putMenuItem( "My PAL Scripts", "pals.php", "images/pal.gif", $i++, LINK_TGT_BLANK );
}

if ($have_top_songs_list_link) {
    putMenuItem( "Top Songs", "top_songs.php", "images/top-songs.gif", $i++, LINK_TGT_BLANK );
}

if (1 == 1 || $have_stats_list_link) {
    putMenuItem( "Stats", "stats.php", "images/top-songs.gif", $i++ );
}

if (false && $have_browse_comments_link) {
    putMenuItem( "Browse Comments", "browse-comments.php", "images/browse-comments.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_playlist_link) {      
    putMenuItem( "Playlist", "playlist.php?limit=10", "images/playlist-requests.gif", $i++, LINK_TGT_BLANK );
}

if (false && $have_processing_queue_link) {
    putMenuItem( "CD Processing Queue", $processing_queue_link_url, $processing_queue_text, $i++, LINK_TGT_BLANK );
}

if ( $have_link_link) {
    // $processing_queue_text is undefined
	//putMenuItem( "Links", $links_link_url, $processing_queue_text, $i++ );
}

if ( $have_gallery_link) {
//    putMenuItem( "Gallery", $links_gallery_url, "", $i++, LINK_TGT_BLANK );
}
?>
			</ul>
		</div>
	</div>