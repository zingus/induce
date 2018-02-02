<?php

ob_start(); include('induce.php'); ob_end_clean();

/*
$zero=$argv[0];
$ok=preg_match('#i(.*)#',basename($zero),$m);
if($ok) $wget=$m[1];
*/

$inducted=array();
$maxLength=null;
$maxArgs=256;
$args=array_slice($argv,1);
// wget forgeries
$forge_user_agent="-U 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'";
$ignore_robots="-e robots=off";

if(PHP_OS=='WINNT') {
  // $maxArgs=null; // IDK, is there a cygwin/mingw limitation here?
  //$maxLength=8191; // shall be ok for XP and further
  $maxLength=2047; // IDK, Windows 2k an previous
}

$cmd="wget -c $forge_user_agent $ignore_robots";
$cmdArgCount=5;

$commandArgs=array();
foreach($args as $arg) {
  if(strstr($arg,'://')) {
    // urls
    $inducted=array_merge($inducted,induce($arg));
  } else {
    // command line options
    $cmd.=" '$arg'";
    $cmdArgCount++;
  }
}

$script=array();
$commandLine="$cmd";
$argCount=$cmdArgCount;
while($inducted) {
  $arg=array_shift($inducted);
  $addenda=" --referer='$arg' '$arg'"; // add argument and forge referer
  $addendaArgCount=2;

  $maxLengthReached = is_null($maxLength) ? false : strlen($commandLine) + strlen($addenda) >= $maxLength;
  $maxArgsReached   = is_null($maxArgs)   ? false : $argCount >= $maxArgs;
  
  if($maxLengthReached or $maxArgsReached) {
    $script[]=$commandLine;
    $commandLine="$cmd $addenda";
    $argCount=$cmdArgCount;
  }
  
  $commandLine.=$addenda;
  $argCount+=$addendaArgCount;
}
$script[]=$commandLine;

// running script

foreach($script as $commandLine) {
  echo $commandLine,"\n";
  system($commandLine);
}

// dumping script
/*
$fp=fopen('wgetScript.bat','w');
foreach($script as $commandLine)
  fwrite($fp,$commandLine."\r\n");
fclose($fp);

// dump ain't working on windows, as each and every call to
// wget.bat would require a "call" command preceding it
*/

