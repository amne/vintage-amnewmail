<?php
include("wm/rd_mail.php");
$lqqr=$_SERVER['QUERY_STRING']; parse_str($lqqr);
if($_COOKIE["logged_in_188"]=="yess")
 {
 $itusername=$_COOKIE["it_username"];
 $ippassword=$_COOKIE["ip_password"];
 $login=2;
 }
if($logout==1)
 {
 $login=0;
 setcookie("logged_in_188","");
 }
if(!$login)
 {
 include_once("login.php");
 }
if($login)
{
if(!$viewattach)
 {
  $viewattach="listmsgs";
 }
$blh=new ai_map();
if($_POST["is_login"]=="Login")
 {
 $itusername=$_POST["it_username"];
 $ippassword=$_POST["ip_password"];
 setcookie("it_username",$itusername,time()+1800);
 setcookie("ip_password",$ippassword,time()+1800);
 setcookie("logged_in_188","yess",time()+1800);
 }
$blh->init("mailserver.tld",$itusername,$ippassword,":143",$mailbbox);

print "\n".'<b> You are logged in as '.$itusername.'.';
print "\n";
if($viewattach=="view")
 {
 include("viewattc.php");
 }
if($viewattach=="listmsgs")
 {
 include("listmsgs.php");
 }
}
?>
