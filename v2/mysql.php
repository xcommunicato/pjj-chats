<?php
/*
if ($_SERVER['REMOTE_ADDR'] !== '83.92.21.119') {
	die('Updating to Unicode - check back in a few hours.');
}
//*/

//*
function xm() {
	list($x, $y) = explode(' ', microtime());
	return ((float)$x + (float)$y);
}
//*/
/*
	$t = xm();
	echo '<!-- '.(xm()-$t).' -->';
	require_once('/home/chats/public_html/v3/_inc/mmcache.php');
	MMC_Lock($_SERVER['REMOTE_ADDR']);
//*/

/*
	$load = floatval(shell_exec('cat /proc/loadavg'));
	if ($load >= 60) {
		die('Load too high, try again later.');
	}
//*/

ignore_user_abort(true);

require_once(__DIR__.'/common/pgsql/sql.php');

$GLOBALS['handler'] = FALSE;

function SQLConnect() {
	$db_server	= "localhost";
	$db_username	= "root";
	$db_password	= "";
	$db_database	= "pjj_chats";
	if (file_exists(__DIR__.'/mysql-secret.php')) {
		include __DIR__.'/mysql-secret.php';
	}

	$GLOBALS['handler'] = @mysql_pconnect($db_server, $db_username, $db_password);
	if ($GLOBALS['handler'] === FALSE) {
		echo '<b>MySQL connection failed. Trying to recover...</b><p>';
		usleep(200000);
		$GLOBALS['handler'] = @mysql_pconnect($db_server, $db_username, $db_password);
		if ($GLOBALS['handler'] === FALSE) {
			header('Refresh: 10; URL='.$_SERVER['REQUEST_URI']);
			echo '<meta http-equiv="Refresh" content="10; URL='.$_SERVER['REQUEST_URI'].'">';
			echo '<p><b>The MySQL server is down or temporarily malfunctioning. <a href="javascript:history.go(0);">Reload the page.</a></b><p>';
//			echo '<br><p><b><a href="http://board.projectjj.com/viewtopic.php?t=625">Read Me: Power Outage</a></b></p>';
			die();
		}
	}
	mysql_set_charset('utf8');
	mysql_select_db($db_database, $GLOBALS['handler']);
}

if (!function_exists('count_mysql_query')) {
	function count_mysql_query($query, $hand, $reason='Unknown') {
		global $cqs;
		$cqs++;
		$err = 0;

		$GLOBALS['querylog'][$cqs]['T'] = xm();
		$GLOBALS['querylog'][$cqs]['Q'] = $query;
		$GLOBALS['querylog'][$cqs]['L'] = $reason;
		$rez = @mysql_query($query, $hand);
		if ($err = @mysql_errno()) {
			if ($err == 1062)
			    return $rez;
			echo '<br><b>MySQL Error '.$err.' occured. Trying to recover...</b><p>';
			@mysql_close();
			SQLConnect();
			$rez = @mysql_query($query, $hand);
			$ere=0;
			if ($ere = @mysql_errno()) {
				@mail('mysql@projectjj.com', 'MySQL Error: '.$err.','.$ere, $err.','.$ere.' occured at '.date('g:ia, F d (T)')." for query:\n".$query);
				die('<br><b>Could not recover. Secondary error '.$ere.' occured.</b>');
			}
		}
		$GLOBALS['querylog'][$cqs]['T'] = xm()-$GLOBALS['querylog'][$cqs]['T'];
		return $rez;
	}
}

if (!function_exists('mq')) {
	function mq($e) {
		$e = trim($e);
		if (empty($e)) {
			return 'null';
		}
		return "'".mysql_real_escape_string($e)."'";
	}
}

SQLConnect();

if (!empty($_COOKIE['X-pJJ-Session'])) {
	mysql_query("INSERT INTO tracker (track_ip) VALUES ('".$_SERVER['REMOTE_ADDR']."')");
}

$handler = $GLOBALS['handler'];
