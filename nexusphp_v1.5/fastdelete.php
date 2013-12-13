<?php
require_once("include/bittorrent.php");
//include_once("removeapi.php");
dbconn();
require_once(get_langfile_path());
require_once(get_langfile_path("",true));
loggedinorreturn();
function bark($msg) {
  global $lang_fastdelete;
  stdhead();
  stdmsg($lang_fastdelete['std_delete_failed'], $msg);
  stdfoot();
  exit;
}

function hex_esc($matches) {
          return sprintf("%02x", ord($matches[0]));
}

//remove seeding torrent from transmission**********************************

function getRemoveItem($id){
//$id="106"; //for test
//$dbcc=mysql_connect("localhost","root","buptnic");
//mysql_query("SET NAMES UTF8");
//mysql_select_db("nexusphp",$dbcc);
//$outcome=mysql_query("SELECT * FROM torrents WHERE id='$id'",$dbcc);
$outcome=mysql_query("SELECT * FROM torrents WHERE id='$id'");
while($info=mysql_fetch_assoc($outcome)){
    $vodhash1=$info["info_hash"];
    $vodsmalldes=$info["small_descr"]; 

}
mysql_close($dbcc);

$vodhash=preg_replace_callback('/./s', "hex_esc", hash_pad($vodhash1));



$fields['vodhash']=$vodhash;
$fields['vodsmalldes']=$vodsmalldes;
$url="222.199.184.41/transmission/removeseed.php";

$ch=curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1 );
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$output=curl_exec($ch);

if($output===false){
   echo curl_error($ch);
}
curl_close($ch);

//echo $output;
}

//**************************************************************************


if (!mkglobal("id"))
    bark($lang_fastdelete['std_missing_form_data']);

$id = 0 + $id;
int_check($id);
$sure = $_GET["sure"];

$res = sql_query("SELECT name,owner,seeders,anonymous FROM torrents WHERE id = $id");
$row = mysql_fetch_array($res);
if (!$row)
    die();

if (get_user_class() < $torrentmanage_class)
    bark($lang_fastdelete['text_no_permission']);

if (!$sure)
	{
	stderr($lang_fastdelete['std_delete_torrent'], $lang_fastdelete['std_delete_torrent_note']."<a class=altlink href=fastdelete.php?id=$id&sure=1>".$lang_fastdelete['std_here_if_sure'],false);
	}
getRemoveItem($id);
deletetorrent($id);


KPS("-",$uploadtorrent_bonus,$row["owner"]);
if ($row['anonymous'] == 'yes' && $CURUSER["id"] == $row["owner"]) {
	write_log("Torrent $id ($row[name]) was deleted by its anonymous uploader",'normal');
} else {
	write_log("Torrent $id ($row[name]) was deleted by $CURUSER[username]",'normal');
}
//Send pm to torrent uploader
if ($CURUSER["id"] != $row["owner"]){
	$dt = sqlesc(date("Y-m-d H:i:s"));
	$subject = sqlesc($lang_fastdelete_target[get_user_lang($row["owner"])]['msg_torrent_deleted']);
	$msg = sqlesc($lang_fastdelete_target[get_user_lang($row["owner"])]['msg_the_torrent_you_uploaded'].$row['name'].$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_was_deleted_by']."[url=userdetails.php?id=".$CURUSER['id']."]".$CURUSER['username']."[/url]".$lang_fastdelete_target[get_user_lang($row["owner"])]['msg_blank']);
	sql_query("INSERT INTO messages (sender, receiver, subject, added, msg) VALUES(0, $row[owner], $subject, $dt, $msg)") or sqlerr(__FILE__, __LINE__);
}
//error_log($id,"3","/var/www/nexusphp/error.log");
//-----------------------
//同步到eku，同时删除
$con = mysql_connect("localhost","root","yourpassword");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_query("set names utf8");
mysql_select_db("ekucms", $con);
$sql="delete from eku122x_video where nexusphp=$id";
//error_log($id,"3","/var/www/nexusphp/error.log");
$result=mysql_query($sql);
mysql_close($con);
//-----------------------

header("Refresh: 0; url=torrents.php");
?>
