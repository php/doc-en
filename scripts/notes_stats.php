<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2010 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Mehdi Achour <didou@php.net> (Original Author)           |
  |             Vincent Gevers <vincent@php.net>                         |          
  +----------------------------------------------------------------------+

 $Id$
*/

/*
 * Warning: This script uses a lot of memory 
 *
 * Usage:
 * $ php notes_stats.php [mbox-file] > notes.php
 */
 
/*
 * TODO:
 * - Caching the articles (SQLite?)
 * - Speed improvements
 * - Nicer layout
 */

// minimum amount actions
$minact = 100;
// after how many secs should the list be chopped
$after = 182.5*24*60*60; // half year

$inputs = array(); // pair subjects w/ dates from multiple sources

$time_start = getmicrotime();

if (isset($argv[1]) && file_exists($argv[1])) { // from file

    $lines = file($argv[1]);
    $count = count($lines);
    
    for ($i = 0; $i < $count; ++$i) {
        list($time, $subj) = explode(' ', $lines[$i], 2);
        
        $inputs[] = array($time, substr($subj, 12));
    }
    
} elseif (isset($argv[1])) {

    echo "File doesn't exist!";
    exit(1);
 
} else { // from nntp

 $s = nntp_connect("news.php.net")
   or die("failed to connect to news server");

 $res = nntp_cmd($s,"GROUP php.notes",211)
   or die("failed to get infos on news group");

 $new = explode(" ", $res);
 $first = 1;
 $last =  $new[0];

 //$first = 69900;
 //$last =  70000;

 $res = nntp_cmd($s,"XOVER $first-$last", 224)
     or die("failed to XOVER the new items");

 for ($i = $first; $i < $last; $i++) {
 
     $line = fgets($s, 4096);
     list($n,$subj,$author,$odate,$messageid,$references,$bytes,$lines,$extra)= explode("\t", $line, 9);
     $inputs[] = array($odate, $subj);
 }

}

$files = $team = $tmp = array();

$in_old = false;

/*
 * What should be matched:
 * note ID deleted from SECTION by EDITOR
 * note ID rejected from SECTION by EDITOR
 * note ID modified in SECTION by EDITOR
 * note ID moved from SECTION to SECTION by EDITOR (not matched yet)
 */

$reg = '/^note (\d*) (.*) (?:from|in) (\S*) by (\w*)/';

reset($inputs);

while (list( , $inp) = each($inputs)) {
    list($odate, $subj) = $inp;

    if (preg_match($reg, $subj, $d)) {
        if ($d[2] == 'rejected and deleted')
            $d[2] = 'rejected';
        if ($d[2] == 'approved')
            continue;
         
        if(calc_time($odate)) {
            // 'new' before $after
            @$team['n'][$d[4]]['total']++;
            @$team['n'][$d[4]][$d[2]]++; 
            @$tmp['n'][$d[4]]++;
            @$files['n'][$d[3]]++;
        } else {
            // 'old' after $after
            @$team['o'][$d[4]][$d[2]]++; 
            @$tmp['o'][$d[4]]++;
            @$team['o'][$d[4]]['total']++; 
            @$files['o'][$d[3]]++;
        }
        
        // the normal arrays
        @$team[$d[4]]['total']++;
        @$team[$d[4]][$d[2]]++; 
        @$tmp[$d[4]]++;
        @$files[$d[3]]++;
 
    } // end if(preg_match
} // end while(each


ksort($team);
arsort($files);
arsort($tmp);
arsort($tmp['n']);
arsort($tmp['o']);


echo '<html>
<head>
<title>Note Statistics for '.date('j F Y').'</title>
</head>
<body>
';

echo '
<b>' . array_sum($files) . '</b> subjects parsed<br /><br />
';

?>
<table border='0' cellspacing="10"><tr valign="top"><td valign="top">
<table border='1'>
    <tr>
        <td colspan="5" align="center">Editors Stats with more than <?php echo $minact; ?> actions</td>
    </tr>
    <tr>
        <td>user</td>
        <td>deleted</td>
        <td>rejected</td>
        <td>modified</td>
        <td>total</td>
    </tr>

<?php

$bg = '#EBEBEB';
foreach ($tmp as $user => $total) {
    if($user == 'o' or $user =='n')
       continue;

    if($total >= $minact) { 
        echo "<tr bgcolor=\"";
        $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB';
        echo "$bg\">\n\t<td>$user</td>\n\t<td>";
        echo isset($team[$user]['deleted']) ? $team[$user]['deleted'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team[$user]['rejected']) ? $team[$user]['rejected'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team[$user]['modified']) ? $team[$user]['modified'] : '0';
        echo "</td>\n\t<td>";
        echo $total;
        echo "</td>\n</tr>\n";
    }
    
}

?>
</table>

</td><td valign="top">
Last half year (with more than <?php echo $minact; ?> actions counted)
<table border='1'>
    <tr>
        <td colspan="5" align="center">Recent Editors stats</td>
    </tr>
    <tr>
        <td>user</td>
        <td>deleted</td>
        <td>rejected</td>
        <td>modified</td>
        <td>total</td>
    </tr>

<?php

$bg = '#EBEBEB';
foreach ($tmp['n'] as $user => $total) {

    if($total >= $minact) {    
        echo "<tr bgcolor=\"";
        $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB';
        echo "$bg\">\n\t<td>$user</td>\n\t<td>";
        echo isset($team['n'][$user]['deleted']) ? $team['n'][$user]['deleted'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team['n'][$user]['rejected']) ? $team['n'][$user]['rejected'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team['n'][$user]['modified']) ? $team['n'][$user]['modified'] : '0';
        echo "</td>\n\t<td>";
        echo $total;
        echo "</td>\n</tr>\n";
    }

}


?>
</table>

<br />
Before the last half year (with more than <?php echo $minact; ?> actions counted)
<table border='1'>
    <tr>
        <td colspan="5" align="center">Older Editors Stats</td>
    </tr>
    <tr>
        <td>user</td>
        <td>deleted</td>
        <td>rejected</td>
        <td>modified</td>
        <td>total</td>
    </tr>

<?php

$bg = '#EBEBEB';
foreach ($tmp['o'] as $user => $total) {

    if($total >= $minact) {
        echo "<tr bgcolor=\"";
        $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB';
        echo "$bg\">\n\t<td>$user</td>\n\t<td>";
        echo isset($team['o'][$user]['deleted']) ? $team['o'][$user]['deleted'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team['o'][$user]['rejected']) ? $team['o'][$user]['rejected'] : '0';
        echo "</td>\n\t<td>";
        echo isset($team['o'][$user]['modified']) ? $team['o'][$user]['modified'] : '0';
        echo "</td>\n\t<td>";
        echo $total;
        echo "</td>\n</tr>\n";
    }

}


?>
</table>
</td></tr></table>


<br />

<table border="0" cellspacing="10"><tr valign="top"><td valign="top">
<table border='1'>
    <tr>
        <td colspan="5" align="center">Total Editors Stats</td>
    </tr>
    <tr>
        <td>user</td>
        <td>deleted</td>
        <td>rejected</td>
        <td>modified</td>
        <td>total</td>
    </tr>

<?php

$bg = '#EBEBEB';

foreach ($team as $user => $actions) {
    if($user == 'o' or $user =='n' or $user == '')
       continue;
       
    echo "<tr bgcolor=\"";
    $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB';
    echo "$bg\">\n\t<td>$user</td>\n\t<td>";
    echo isset($actions['deleted']) ? $actions['deleted'] : '0';
    echo "</td>\n\t<td>";
    echo isset($actions['rejected']) ? $actions['rejected'] : '0';
    echo "</td>\n\t<td>";
    echo isset($actions['modified']) ? $actions['modified'] : '0';
    echo "</td>\n\t<td>";
    echo $actions['total'];
    echo "</td>\n</tr>\n";

}


?>
</table>

</td><td valign="top">
<table border='1'>
    <tr>
        <td colspan="3" align="center">Editors top 15</td>
    </tr>
    <tr>
        <td>rank</td>
        <td>user</td>
        <td>total</td>
    </tr>
<?php
$i = 0;
    foreach($tmp as $k => $v) {
    if($k == 'o' or $k =='n')
       continue;
       
        $i++;
        echo "<tr bgcolor=\"";
        $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB'; 
        echo "$bg\">\n\t<td>$i</td>\n\t<td>$k</td>\n\t<td>$v</td>\n</tr>\n";
    
    if ($i == 15)
        break;
}       

?>
</table>

<br />
<table border='1'>
    <tr>
        <td colspan="3" align="center">Manual pages most active top 20</td>
    </tr>
    <tr>
        <td>rank</td>
        <td>page</td>
        <td>total</td>
    </tr>
<?php
$i = 0;
    foreach($files as $k => $v) {
    if($k == 'o' or $k =='n')
       continue;
       
        $i++;
        echo "<tr bgcolor=\"";
       $bg = ($bg == '#EBEBEB') ? '#BEBEBE' : '#EBEBEB'; 
        echo "$bg\">\n\t<td>$i</td>\n\t<td>$k</td>\n\t<td>$v</td>\n</tr>\n";
    
    if ($i == 20)
        break;
}       

?>
</table>

</td></tr></table>

<?php
 $time_end = getmicrotime();
 $time = $time_end - $time_start;
 
 echo "All done in $time seconds\n";
 ?>
</body>
</html>

<?php

function nntp_connect($server,$port=119) {
  $s = fsockopen($server,$port,$errno,$errstr,30);

  if (!$s) {
    echo "<!-- error connecting to nntp server: $errstr -->\n";
    return false;
  }
  $hello = fgets($s, 1024);
  if (substr($hello,0,4) != "200 ") {
    echo "<!-- unexpected greeting: $hello -->\n";
    return false;
  }
  #echo "<!-- $hello -->\n";
  return $s;
}

function nntp_cmd($conn,$command,$expected) {
  if (strlen($command) > 510) die("command too long: $command");
  fputs($conn, "$command\r\n");
  $res = fgets($conn, 1024);
  list($code,$extra) = explode(" ", $res, 2);
  return $code == $expected ? $extra : false;
}

function getmicrotime() { 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 

function calc_time ($before) {
global $after;

// simple caching
if(@$in_old == true) 
   return false;

if (!is_numeric($before)) {
    $before = strtotime($before);
}

$afterall =  $before - (time() - $after);

if($afterall > 0) {
    // more then $after
    $in_old = false;    
    return true;
} else {
    // older then $after
    $in_old = true;
    return false;
}

}


?>
