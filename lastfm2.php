<? 
/**************************************************************** 
Audioscrobbler song submitter for SpacialAudio's SAM Broadcaster 

by: disq <disqkk@gmail.com> 
version: 0.1 (released 20070102) 

requirements: 
SAM (tested with SAM4 but should work ok with previous versions) 
a html template in SAM, see below 
PHP command-line, with curl module enabled (you'll have to edit php.ini, see php installation docs) 

SAM html template to use: 
--cut-- 
|$song.title$ 
|$song.artist$ 
|$song.album$ 
|$song.mmss$ 
--cut-- 

please change the parameters below 
*/ 

//path to the html file sam generates (template is above) 
DEFINE(htmlfile, 'lastfm2.html'); 

//path to logfile if you want logs 
DEFINE(logfile, 'lastfm2.txt'); 

//your last.fm username 
DEFINE(lastfmuser, 'progscout'); 

//your last.fm password (you can remove the "md5" expression and add the md5 of the password) 
DEFINE(lastfmpassmd5, '583216fcb3d3a5061933978bd47b1045');  

//http timeout, 5-10 is ok 
DEFINE(timeoutsecs, 10); 

/* 
ok that's all, you don't have to change anything below *****************************************************************/

DEFINE(clientid, "sam");
DEFINE(clientver, "0.1");

set_time_limit(30);

function logline($line, $writetofile=1)
{
$ls=date ("y-m-d H:i:s")." | ".$line."\n";
echo $ls;
if ($writetofile!=1 || logfile=="") return(1);

$f=@fopen(logfile, "a");
if ($f!=0)
   {
   flock($f, 2);
   fputs($f, $ls);
   flock($f, 3);
   fclose($f);
   return(0);
   }
return(1);
}

function geturl($url, $fixCRLF=1)
{
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, timeoutsecs);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$o=curl_exec($ch);
curl_close($ch);

if ($fixCRLF==1)
   {
   $o=str_replace("\r", "\n", $o);
   $o=str_replace("\n\n", "\n", $o);
   }
return($o);
}

function login()
{

   $ts=time();
   $r=array(0=>"unknown", "", "", "");

   $ret=geturl("http://post.audioscrobbler.com/?hs=true&p=1.1&c=".rawurlencode(clientid). "&v=".rawurlencode(clientver)."&u=".rawurlencode(lastfmuser)."&t=".$ts);

   $r=explode("\n", $ret);

   $t=explode(" ", $r[0]);

   if ($t[0]=="UPDATE")
      {
      $r[0]="UPTODATE";
      break;
      }
   if ($r[0]=="BADAUTH" || $r[0]=="BADUSER")
      {
      //logline("ERROR: Authentication failed, check lastfmuser/lastfmpass");
      exit;
      }

    if ($r[0]=="UPTODATE")
   {
   $t=explode(" ", $r[3]);
   if ($t[0]=="INTERVAL")
           {
           $r[3]=(int)$t[1];
           }
   }
    else
   {
   //logline("ERROR: unknown");
   exit;
   }

  return($r);
}

unset($submitqueue);

function checksubmitsong($t)
{
  global $lo, $submitqueue;

  @reset($submitqueue);
  while($fi=@each($submitqueue))
   {
   $ti=$fi[key];
   if ($t>$ti)
      {
      $o=submitsong($fi[value]);
      //logline("submitsong returned $o (int=".$lo[3].")");
      if ($o=="OK")
         {
         unset($submitqueue[$ti]);
                        exit;
         }
      }
   }
}

$lastsubmittime=0;

function submitsong($info, $nowplaying=0 /*1.2*/, $recurse=1)
{
global $lo, $lastsubmittime;

if ($lastsubmittime>0 && $lo[3]>0)
   {
   if ($lastsubmittime+$lo[3]>time()) return("WAIT");
   }

$app=" for stats";
if ($nowplaying!=0) { $app=" for now playing"; $nowplaying=1; }

//logline("submitting ".$info[0]."/".$info[1]."/".$info[2]."/".$info[3].$app);
$so=$info[0]."/".$info[1];

$info[0]=utf8_encode($info[0]);
$info[1]=utf8_encode($info[1]);
$info[2]=utf8_encode($info[2]);
$info[3]=utf8_encode($info[3]);

$pf="u=".urlencode(lastfmuser)."&s=".urlencode(md5(lastfmpassmd5.$lo[1]))."&a[0]= ".urlencode($info[1])."&t[0]=".urlencode($info[0])."&b[0]= ".urlencode($info[2])."&m[0]=&l[0]=".urlencode($info[4])."&i[0]= ".urlencode(gmdate("Y-m-d H:i:s", $info[5]))."&";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $lo[2]);//1.1
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, timeoutsecs);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pf);

$o=curl_exec($ch);
$od="";

if (curl_errno($ch)!=0)
   {
   $o="curlERR";
   $od=curl_error($ch);
   }
curl_close($ch);

$oo=explode("\n", $o);
$o=$oo[0];

$t=explode(" ", $oo[1]);
if ($t[0]=="INTERVAL") $lo[3]=(int)$t[1];

if ($o!="OK")
   {
   //logline("Warning: Could not submit song (".$so.")".$app);
        exit;
   }

if ($o=="BADAUTH")
   {
   //logline("ERROR: Authentication failed, check lastfmuser/lastfmpass");
   exit;
   }
else if ($o=="curlERR")
   {
   //logline("ERROR: Got error from AS url (".$od.", ".$lo[3].")");
        exit;
   }
else if ($o!="OK")
   {
   //logline("got:$o*");
        exit;
   }

return($o);

exit;
}

$lo=login();
$lastmtime=0;
$submittime=0;
unset($lastinfo);

while(1)
   {
   $t=time();

   checksubmitsong($t);

   clearstatcache();

   $mt=@filemtime(htmlfile);

   $da="";
   $f=@fopen(htmlfile, "r");

   if ($f!=0)
      {
      $da=@fread($f, 4096);
      fclose($f);
      }
   else
      {
      //logline("error opening htmlfile");
      exit;
      }
   $lastmtime=$mt;
   $da=str_replace("\r", "\n", $da);
   $da=str_replace("\n\n", "\n", $da);
   $d=explode("\n", $da);
   $d[0]=trim($d[0], "|"); // title
   $d[1]=trim($d[1], "|"); // artist
   $d[2]=trim($d[2], "|"); // album
   $d[3]=trim($d[3], "|"); // time
   
   $tmp=explode(":", $d[3]);
   $xx=count($tmp);
   $secs=0;
   for($i=$xx-1;$i>=0;$i--)
      {
      $digit=(int)$tmp[$i];
      if ($digit<1) continue;
      $curdi=$xx-$i-1;
      if ($curdi>0) $di=$digit*$curdi*60;
                   else $di=$digit;
      $secs+=$di;
      }

   if ($d[0]=="" && $d[1]=="")
      {
      //logline("skipping song submission for ".$d[0]."/".$d[1]."/".$d[3]);
      exit;
      }

   $d[4]=$secs;
   $d[5]=$t;

        $submittime=$t;
   $submitqueue[$submittime]=$d;

   }

?>