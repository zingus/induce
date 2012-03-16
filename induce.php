#!/usr/bin/env php
<?php
// Name: induce
// Version: 1.0.1
// Author: Zingus J. Rinkle
// Website: http://www.uehb.it/induce

class induce {
  function _expand($data)
  {
    static $backrefs=array();

    // reserve a backref position ($backrefk) //
    $backrefs[]=null;
    end($backrefs);
    $backrefk=key($backrefs);
    
    $branches=array();
    
    $ret=array('');
    preg_match_all('/
      \\\\(?:(\d+)|(.))       # backref | \text1
      |\[(-?\d+)\.\.(-?\d+)\] # range1..range2
      |\[(.*?)\]              # crange
      |(\((?:[^()]+|(?6))*\)) # braces
      |(\|)                   # bar
      |([^\[\(\|\\\\]*)       # text
    /sx',$data,$M,PREG_SET_ORDER);
    foreach($M as $m) {
      list(,$backref,$text1,$range1,$range2,$crange,$braces,$bar,$text)=$m;
      if ($backref) {
        /* echo "<$backref> ";
        var_export($backrefs); echo "\n";
        var_export($backrefs); echo "\n";
        echo "\n";*/
      }

      if (strlen($text1)) $text=$text1;
      if (strlen($text)) foreach($ret as $k=>$v) $ret[$k].=$text;
      
      if ($bar) {
        $branches[]=$ret;
        $ret=array('');
        continue;
        // ok, still to figure this out, comrades
        //array_merge($ret,induce::_expand($next_expr));
      }

      if(is_numeric($range1))
        $braces=induce::_range($range1,$range2);
      elseif($crange)
        $braces=induce::_crange_parse($crange);
      elseif($braces) {
        $braces=substr($braces,1,-1);
        $braces=induce::_expand($braces);
      }
      
      if ($braces){
        $ret2=array();
        foreach($ret as $k=>$v) {
          //unset($ret[$k]);
          foreach($braces as $bracek=>$brace){
              $ret2[]=$v.$brace;
          }
        }
        $ret=$ret2;
      }

      if ($backref) { 
        $len=count($backrefs[$backref]);
        foreach($ret as $k=>$v) {
          $ret[$k].=$backrefs[$backref][$k%$len];
        }
      }

    }
    
    foreach($branches as $branch) {
      $ret=array_merge($branch,$ret);
    }
    $backrefs[$backrefk]=$ret;
    return $ret;
  }

  function _range($a,$b)
  {
    $ret=array();
    
    if($a[0]=='0')
      $len=strlen($a);
    elseif( $a[0]=='-' and $a[1]=='0' )
      $len=strlen($a)-1;
    elseif($b[0]=='0')
      $len=strlen($b);
    elseif( $b[0]=='-' and $b[1]=='0' )
      $len=strlen($b)-1;

    if ($a<$b)
      for($i=$a;$i<=$b;$i++)
        $ret[]=induce::_intpad($i,$len);
    else
      for($i=$a;$i>=$b;$i--)
        $ret[]=induce::_intpad($i,$len);

    return $ret;
  }

  function _intpad($int,$len)
  {
    list($ret,$neg)=explode('-',$int);
    if($neg) $ret=$neg;
    $ret=($neg?'-':'').str_pad($ret,$len,'0',STR_PAD_LEFT);
    return $ret;
  }

  function _crange_parse($crange) {
    preg_match_all('#\\\\?(.)-\\\\?(.)|\\\\?(.)#',$crange,$M,PREG_SET_ORDER);
    $ret=array();
    foreach($M as $m) {
      list(,$a,$b,$c)=$m; 
      if ($c) {
        $ret[]=$c;
        continue;
      }

      $a=ord($a);
      $b=ord($b);
      
      if ($a<$b)
        for($i=$a;$i<=$b;$i++) $ret[]=chr($i);
      else
        for($i=$a;$i>=$b;$i--) $ret[]=chr($i);
    }
    return $ret;
  }
}

function induce($data)
{
  return induce::_expand($data);
}

// main
if(realpath($argv[0])==__FILE__) {
  $data=implode(' ',array_slice($argv,1));
  foreach(induce($data) as $line) echo "$line\n";
}

/*/
$a=induce::_range('0','15');
$b=array (
  0 => '0',
  1 => '1',
  2 => '2',
  3 => '3',
  4 => '4',
  5 => '5',
  6 => '6',
  7 => '7',
  8 => '8',
  9 => '9',
  10 => '10',
  11 => '11',
  12 => '12',
  13 => '13',
  14 => '14',
  15 => '15',
);
$PHT->assert($a==$b,'induce::_range 0-15');

$a=induce::_range('002',15);
$b=array (
  0 => '002',
  1 => '003',
  2 => '004',
  3 => '005',
  4 => '006',
  5 => '007',
  6 => '008',
  7 => '009',
  8 => '010',
  9 => '011',
  10 => '012',
  11 => '013',
  12 => '014',
  13 => '015',
);
$PHT->assert($a==$b,'induce::_range 002-015');

$a=induce("a[10..20]b");
$b=array ('a10b','a11b','a12b','a13b','a14b','a15b','a16b','a17b','a18b','a19b','a20b',);
$PHT->assert($a==$b,'range expansion');

$a=induce("a[0..3]b");
$b=array ('a0b','a1b','a2b','a3b',);
$PHT->assert($a==$b,'range expansion 0-3');


$a=induce('a[bcde\fg]h');
$b=array ('abh','ach','adh','aeh','afh','agh',);
$PHT->assert($a==$b,'character range expansion');



/*/
