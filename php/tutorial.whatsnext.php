<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP ¤â¥U'),
  'prev' => array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
  'next' => array('installation.php', '¦w¸Ë'),
  'up'   => array('tutorial.php', 'A simple tutorial'),
  'toc'  => array(
       array('tutorial.php#tutorial.requirements', 'What do I need?'),
     array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
     array('tutorial.useful.php', 'Something Useful'),
     array('tutorial.forms.php', 'Dealing with Forms'),
     array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
     array('tutorial.whatsnext.php', 'What\'s next?'),
)));
manualHeader('What\'s next?','tutorial.whatsnext.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.whatsnext"
></A
>What's next?</H1
><P
>&#13;    With what you know now you should be able to understand most of 
    the manual and also the various example scripts available in the
    example archives. You can also find other examples on the php.net
    websites in the links section:
    <A
HREF="http://www.php.net/links.php"
TARGET="_top"
>http://www.php.net/links.php</A
>.
   </P
></DIV
><?php manualFooter('What\'s next?','tutorial.whatsnext.php');
?>