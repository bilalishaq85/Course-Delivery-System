<?php
ob_start(); ini_set('output_buffering','1'); 
if (!isset($_SESSION)) {session_start();}
//require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
manUserVar("INIT");
manQuizVar("CLEAN");
manQuestionVar("CLEAN");
echo "<script>top.window.location = 'index.php'</script>";
ob_flush(); 
exit;
//header('Location: ' . $_SERVER['HTTP_REFERER']); ob_flush(); exit;
//header('Location:index.php'); ob_flush(); exit;
?>
