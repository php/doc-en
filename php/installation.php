<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP 手冊'),
  'prev' => array('tutorial.whatsnext.php', 'What\'s next?'),
  'next' => array('install.hpux.php', 'Unix/HP-UX installs'),
  'up'   => array('getting-started.php', '入門'),
  'toc'  => array(
      array('introduction.php', 'Introduction'),
      array('tutorial.php', 'A simple tutorial'),
      array('installation.php', '安裝'),
      array('configuration.php', 'Configuration'),
      array('security.php', 'Security'),
)));
manualHeader('安裝','installation.php');
?><DIV
CLASS="chapter"
><H1
><A
NAME="installation"
></A
>&#31456; 3. 安裝</H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>&#20839;&#23481;&#30446;&#37636;