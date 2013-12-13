<?php
require "include/bittorrent.php";
require("config/allconfig.php");
//$ip=getip();
//$name=str_replace('.','',$ip);
//--------------------------
//读取a1.txt中的数字作为用户名
$logfile    = 'ekucms/count/logs/a1.txt';
/* Does the log exist? */
if (file_exists($logfile)) {

	/* Get current count */
	$count = intval(trim(file_get_contents($logfile))) or $count = 0;
	$cname = 'gcount_unique_'.$page;

	if ($count_unique==0 || !isset($_COOKIE[$cname]))
    {
		/* Increase the count by 1 */
		$count = $count + 1;
//echo $count;
		$fp = @fopen($logfile,'w+') or die('ERROR: Can\'t write to the log file ('.$logfile.'), please make sure this file exists and is CHMOD to 666 (rw-rw-rw-)!');
		flock($fp, LOCK_EX);
		fputs($fp, $count);
		flock($fp, LOCK_UN);
		fclose($fp);

		/* Print the Cookie and P3P compact privacy policy */
		header('P3P: CP="NOI NID"');
		setcookie($cname, 1, time()+60*60*$unique_hours);
	}

    /* Is zero-padding enabled? */
    if ($min_digits > 0)
    {
        $count = sprintf('%0'.$min_digits.'s',$count);
    }

    /* Print out Javascript code and exit */

}
else
{
    die('ERROR: Invalid log file!');
}

/* This functin handles input parameters making sure nothing dangerous is passed in */
function input($in)
{
    $out = htmlentities(stripslashes($in));
    $out = str_replace(array('/','\\'), '', $out);
    return $out;
}

//-------------------------
$name=$count;
creatUser($name);
$passk=setPasskey($name);


echo $passk;



function creatUser($name){
$url=$BASIC['BASEURL']."/takesignup.php";

$data=array(
        'wantusername'=>$name,
        'wantpassword'=>'nexusphp',
        'passagain'=>'nexusphp',
        'email'=>'',
        'gender'=>'Male',
        'faqverify'=>'yes',   
        'ageverify'=>'yes',
        'rulesverify'=>'yes',
        'hash'=>'',
        'country'=>'8',
        'school'=>'35',
); 

$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

$result=curl_exec($ch);

curl_close($ch);
}



function setPasskey($name){
/*
$dbconnnexus=mysql_connect("localhost", "root", "buptnic");
if (!$dbconnnexus){
   die('Could not connect');
}
mysql_query("SET NAMES UTF8");
mysql_select_db("nexusphp",$dbconnnexus);
*/

require_once("include/bittorrent.php");
dbconn(true);
$result=mysql_query("SELECT * FROM users WHERE username = '$name'",$dbconnnexus);
             
     while($info=mysql_fetch_assoc($result)){
         $passkeyvalue=$info["passkey"];
         $passhash=$info["passhash"]; 
         //echo "</br>passhash is this: ".$passhash;
         //echo "</br>passkeyvalue is this: ".$passkeyvalue;
     }

     if(!$passkeyvalue){
         $passkey=md5($name.date("Y-m-d H:i:s").$passhash);
         mysql_query("UPDATE users SET passkey = '$passkey' WHERE username = '$name'");
     }
     else return $passkeyvalue;


//mysql_close($dbconnnexus);

return $passkey;
}


?>

