<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP 手冊'),
  'prev' => array('intro-whatcando.php', 'What can PHP do?'),
  'next' => array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
  'up'   => array('getting-started.php', '入門'),
  'toc'  => array(
      array('introduction.php', 'Introduction'),
      array('tutorial.php', 'A simple tutorial'),
      array('installation.php', '安裝'),
      array('configuration.php', 'Configuration'),
      array('security.php', 'Security'),
)));
manualHeader('A simple tutorial','tutorial.php');
?><DIV
CLASS="chapter"
><H1
><A
NAME="tutorial"
></A
>&#31456; 2. A simple tutorial</H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>&#20839;&#23481;&#30446;&#37636;</B
></DT
><DT
><A
HREF="tutorial.php#tutorial.requirements"
>What do I need?</A
></DT
><DT
><A
HREF="tutorial.firstpage.php"
>Your first PHP-enabled page</A
></DT
><DT
><A
HREF="tutorial.useful.php"
>Something Useful</A
></DT
><DT
><A
HREF="tutorial.forms.php"
>Dealing with Forms</A
></DT
><DT
><A
HREF="tutorial.oldcode.php"
>Using old code with new versions of PHP</A
></DT
><DT
><A
HREF="tutorial.whatsnext.php"
>What's next?</A
></DT
></DL
></DIV
><P
>&#13;   Here we would like to show the very basics of PHP in a short simple
   tutorial. This text only deals with dynamic webpage creation with
   PHP, though PHP is not only capable of creating webpages. See
   the section titled <A
HREF="intro-whatcando.php"
>What can PHP
   do</A
> for more information.
  </P
><P
>&#13;   PHP-enabled web pages are treated just like regular HTML pages and
   you can create and edit them the same way you normally create
   regular HTML pages.
  </P
><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.requirements"
></A
>What do I need?</H1
><P
>&#13;    In this tutorial we assume that your server has support for PHP
    activated and that all files ending in <TT
CLASS="filename"
>.php</TT
>
    are handled by PHP. On most servers this is the default extension
    for PHP files, but ask your server administrator to be sure. If
    your server supports PHP then you don't need to do anything. Just
    create your <TT
CLASS="filename"
>.php</TT
> files and put them in your
    web directory and the server will magically parse them for you.
    There is no need to compile anything nor do you need to install
    any extra tools. Think of these PHP-enabled files as simple HTML
    files with a whole new family of magical tags that let you do all
    sorts of things.
   </P
></DIV
></DIV
><?php manualFooter('A simple tutorial','tutorial.php');
?>