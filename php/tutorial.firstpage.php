<?php 
require('shared-manual.inc');
sendManualHeaders('ISO-8859-1','zh_tw');
setupNavigation(array(
  'home' => array('index.php', 'PHP дтеU'),
  'prev' => array('tutorial.php', 'A simple tutorial'),
  'next' => array('tutorial.useful.php', 'Something Useful'),
  'up'   => array('tutorial.php', 'A simple tutorial'),
  'toc'  => array(
       array('tutorial.php#tutorial.requirements', 'What do I need?'),
     array('tutorial.firstpage.php', 'Your first PHP-enabled page'),
     array('tutorial.useful.php', 'Something Useful'),
     array('tutorial.forms.php', 'Dealing with Forms'),
     array('tutorial.oldcode.php', 'Using old code with new versions of PHP'),
     array('tutorial.whatsnext.php', 'What\'s next?'),
)));
manualHeader('Your first PHP-enabled page','tutorial.firstpage.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="tutorial.firstpage"
></A
>Your first PHP-enabled page</H1
><P
>&#13;    Create a file named <TT
CLASS="filename"
>hello.php</TT
> under your
    webserver root directory with the following content:
   </P
><P
>&#13;    <TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="AEN142"
></A
><P
><B
>&#31684;&#20363; 2-1. Our first PHP script: <TT
CLASS="filename"
>hello.php</TT
></B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="php"
>&#60;html&#62;
 &#60;head&#62;
  &#60;title&#62;PHP Test&#60;/title&#62;
 &#60;/head&#62;
 &#60;body&#62;
 &#60;?php echo "Hello World&#60;p&#62;"; ?&#62;
 &#60;/body&#62;
&#60;/html&#62;</PRE
></TD
></TR
></TABLE
><P
>&#13;      The output of this script will be:
      <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
CELLPADDING="5"
><TR
><TD
><PRE
CLASS="html"
>&#60;html&#62;
 &#60;head&#62;
  &#60;title&#62;PHP Test&#60;/title&#62;
 &#60;/head&#62;
 &#60;body&#62;
 Hello World&#60;p&#62;
 &#60;/body&#62;
&#60;/html&#62;</PRE
></TD
></TR
></TABLE
>
     </P
></DIV
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Note that this is not like a CGI script. The file does not need to be
    executable or special in any way. Think of it as a normal HTML
    file which happens to have a set of special tags available to you
    that do a lot of interesting things.
   </P
><P
>&#13;    This program is extremely simple and you really didn't need to use
    PHP to create a page like this. All it does is display:
    <TT
CLASS="literal"
>Hello World</TT
> using the PHP <A
HREF="function.echo.php"
><B
CLASS="function"
>echo()</B
></A
>
    statement.
   </P
><P
>&#13;    If you tried this example and it didn't output anything, or it prompted 
    for download, or you see the whole file as text, chances are that the 
    server you are on does not have PHP enabled. Ask your administrator 
    to enable it for you using the
    <A
HREF="installation.php"
>Installation</A
> chapter 
    of the manual.  If you want to develop PHP scripts locally, see
    the <A
HREF="http://www.php.net/downloads.php"
TARGET="_top"
>downloads</A
> section.
    You can develop locally on any Operating system, be sure to 
    install an appropriate web server too.
   </P
><P
>&#13;    The point of the example is to show the special PHP tag format.
    In this example we used <TT
CLASS="literal"
>&#60;?php</TT
> to indicate the
    start of a PHP tag. Then we put the PHP statement and left PHP mode by
    adding the closing tag, <TT
CLASS="literal"
>?&#62;</TT
>. You may jump in
    and out of PHP mode in an HTML file like this all you want.
   </P
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>A Note on Text Editors: </B
>
     There are many text editors and Integrated Development Environments (IDEs)
     that you can use to create, edit and manage PHP files. A partial list of 
     these tools is maintained at <A
HREF="http://www.itworks.demon.co.uk/phpeditors.htm"
TARGET="_top"
>PHP Editor's
      List</A
>. If you wish to recommend an editor, please visit the above
     page and ask the page maintainer to add the editor to the list.
    </P
></BLOCKQUOTE
></DIV
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>A Note on Word Processors: </B
>
     Word processors such as StarOffice Writer, Microsoft Word and Abiword are
     not good choices for editing PHP files.
    </P
><P
>&#13;     If you wish to use one for this test script, you must ensure that you save
     the file as PLAIN TEXT or PHP will not be able to read and execute the
     script.
    </P
></BLOCKQUOTE
></DIV
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>A Note on Windows Notepad: </B
>    
     If you are writing your PHP scripts using Windows Notepad, you will need
     to ensure that your files are saved with the .php extension. (Notepad adds
     a .txt extension to files automatically unless you take one of the
     following steps to prevent it.)
    </P
><P
>    
     When you save the file and are prompted to provide a name for the file,
     place the filename in quotes (i.e. "hello.php").
    </P
><P
>&#13;     Alternately, you can click on the 'Text Documents' drop-down menu in the
     save dialog box and change the setting to "All Files". You can then enter
     your filename without quotes.
    </P
></BLOCKQUOTE
></DIV
></DIV
><?php manualFooter('Your first PHP-enabled page','tutorial.firstpage.php');
?>