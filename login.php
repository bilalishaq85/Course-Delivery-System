<?php
ob_start(); ini_set('output_buffering','1'); 
if (!isset($_SESSION)) {session_start();}
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
if (!isset($gVar)) {global $gVar; $gVar = array(); $gVar = getGlobalVar(); }
$_SESSION["LoginMsg"] = "";
$username = trim(strtolower($_POST['username'])); $password = trim($_POST['password']);
if (empty($username) || empty($password)) { 
	$_SESSION["LoginMsg"]="Error: Empty username or password !"; 
	header('Location: ' . $_SERVER['HTTP_REFERER']); 
	ob_flush(); exit; 
}
//Checking members
$qry = " SELECT memCode, memTitle, memFirstName, memLastName, memPassword, memType, memStudents, memVerified, memFailedLogin, memActive";
$qry.= " FROM mMembers";
$qry.= " Where memEmail = '" . strtolower($username) . "'";
#$qry.= " And   memPassword = '" . $password ."'";
#$qry.= " And   memVerified = 1";
#$qry.= " And   memActive = 1";
$result = mysql_query($qry) or die('Error: ' . mysql_error());
$numrows = mysql_num_rows($result);
if ($numrows > 0){
	$row = mysql_fetch_assoc($result);
	if ($row['memVerified'] != 1) { 
		$_SESSION["LoginMsg"]="Error: Email address is not verified!"; 
		header('Location: ' . $_SERVER['HTTP_REFERER']); 
		ob_flush(); exit; 
	}
	if ($row['memActive'] != 1) { 
		$_SESSION["LoginMsg"]="Error: Your account is not active, contact site Admin!"; 
		header('Location: ' . $_SERVER['HTTP_REFERER']); 
		ob_flush(); exit; 
	}
	if ($gVar['MemFailedLoginCnt'] > 0 && $row['memFailedLogin'] > $gVar['MemFailedLoginCnt']) { 
		$_SESSION["LoginMsg"]="Error: Account is locked due to max failed login attempts has reached, click forgot password to reset password."; 
		header('Location: ' . $_SERVER['HTTP_REFERER']); 
		ob_flush(); exit; 
	}
	if ($row['memPassword'] != $password) { 
		$_SESSION["LoginMsg"]="Error: Invalid username or password!";
		recFailedLogin($row['memCode'], $row['memFailedLogin']+1);
		header('Location: ' . $_SERVER['HTTP_REFERER']); 
		ob_flush(); exit;
	}
	if ($row['memFailedLogin'] > 0) { recFailedLogin($row['memCode'], 0);}
	$_SESSION["ValidUser"] = "Y";
	$_SESSION["memType"] = $row['memType'];
	$_SESSION["memCode"] = $row['memCode'];
	$_SESSION["memName"] = $row['memTitle'] . " " . $row['memFirstName'] . " " . $row['memLastName'];
	$_SESSION["memStudents"] = $row['memStudents'];
	#$_SESSION["memName"] = $row['memFirstName'] . " " . $row['memLastName'];
	$_SESSION["memEmail"] = strtolower($username);
	$_SESSION["LoginMsg"] = "";
	$_SESSION["StartTime"] = time();
	header('Location: ' . $_SERVER['HTTP_REFERER']); 
	ob_flush(); exit;
}
//Checking students
//######## 10-SuperUser, 20-Admin, 30-Operator, 40-Member, 50-Student, 60-NoLogin ######
$qry = " SELECT stdCode, memCode, stdFirstName, stdLastName";
$qry.= " FROM mStudent";
$qry.= " Where stdLogin = '" . $username . "'";
$qry.= " And   stdPassword = '" . $password ."'";
$qry.= " And   stdActive = 1";
$result = mysql_query($qry) or die('Error: ' . mysql_error());
$numrows = mysql_num_rows($result);
if ($numrows > 0){
	$row = mysql_fetch_assoc($result);
	$_SESSION["ValidUser"] = "Y";
	$_SESSION["memType"] = 50;
	$_SESSION["stdCode"] = $row['stdCode'];
	$_SESSION["memCode"] = $row['memCode'];
	$_SESSION["stdName"] = $row['stdFirstName'] . " " . $row['stdLastName'];
	$_SESSION["memName"] = $_SESSION["stdName"];
	$_SESSION["LoginMsg"] = "";
	$_SESSION["StartTime"] = time();
	header('Location: ' . $_SERVER['HTTP_REFERER']); 
	ob_flush(); exit;
}
$_SESSION["LoginMsg"]="Error: Invalid username or password !"; 
header('Location: ' . $_SERVER['HTTP_REFERER']); ob_flush(); exit;
ob_flush();
############################
function recFailedLogin($memCode, $memFailedLogin) {
	$qry = " Update mMembers set";
	$qry.= " memFailedLogin = ".$memFailedLogin;
	$qry.= " Where memCode =" . $memCode;
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
}
?>
