#!/usr/bin/env php
<?php

ob_start(); include('induce'); ob_end_clean();

/*
$zero=$argv[0];
$ok=preg_match('#i(.*)#',basename($zero),$m);
if($ok) $wget=$m[1];
*/

$inducted=array();
$maxargs=256;
$optargs=0;
$args=array_slice($argv,1);
// wget forgeries
$forge_user_agent="-U 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'";
$ignore_robots="-e robots=off";

$cmd="wget -c $forge_user_agent $ignore_robots";

$cmd_args=array();
foreach($args as $arg) {
  if(strstr($arg,'://')) {
    // urls
    $inducted=array_merge($inducted,induce($arg));
  } else {
    // command line options
    $optargs++;

    $cmd.=" '$arg'";
  }
}

$scpt="";
while($inducted) {
  $slice=array_splice($inducted,0,$maxargs-$optargs);
  $cmd_args=array();
  foreach($slice as $arg) {
    $cmd_args[]="--referer=$arg"; // forge referer
    $cmd_args[]="$arg";
  }
  $cmd_args=implode("' '",$cmd_args);
  $scpt.="$cmd '$cmd_args' \n";
}

//$scpt.='wait';
echo $scpt;
system("$scpt");
