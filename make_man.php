#!/usr/local/bin/php -q
<?php

/*
 * Script to convert (most of the) php documentation from docbook to unix man format.
 * Roel Vanhout - roel@2e-systems.com - 20010413
 * No long license statements here - do whatever you want.
 */

/*
 * Problems:
 * - examples are not showed correctly
 */ 

$lang = 'en';

$file = `cat \`find phpdoc/$lang | grep .xml\``;
$file = str_replace("\n", '', $file);

// First get everything in <refentry></refentry> tags
preg_match_all('/<refentry.*?<\/refentry>/', $file, $refentries);

$functions = array();
$i = 0;

foreach($refentries[0] as $refentry) {
    preg_match('/<refname>(.*)<\/refname>/', $refentry, $matches);
    $functions[$i]['name'] = $matches[1];
    
    preg_match('/<refpurpose>(.*)<\/refpurpose>/', $refentry, $matches);
    $functions[$i]['shortdesc'] = $matches[1];

    preg_match('/<funcprototype>(.*)<\/funcprototype>/', $refentry, $matches);
    $funcprototype = $matches[1];
    
    preg_match('/<funcdef>(.*)<\/funcdef>/', $funcprototype, $matches);
    $functions[$i]['prototype'] = $matches[1];
    $functions[$i]['prototype'] = preg_replace('/<.*?>/', '', $functions[$i]['prototype']);
    $functions[$i]['prototype'] .= '(';

    preg_match_all('/<paramdef>.*?<\/paramdef>/', $funcprototype, $matches);
    foreach($matches[0] as $param) {
        $param = preg_replace('/\s{2,}/', ' ', $param);
        $functions[$i]['prototype'] .= preg_replace('/<.*?>/', '', $param);
        $functions[$i]['prototype'] .= ', ';
    }
    $functions[$i]['prototype'] = substr($functions[$i]['prototype'], 0, strrpos($functions[$i]['prototype'], ','));
    $functions[$i]['prototype'] .= ')';
    
    $y = 0;
    preg_match_all('/<para>.*?<\/para>/', $refentry, $matches);
    foreach($matches[0] as $paragraph) {
        /* Put every paragraph literally in the man page. Whoever has 
           an idea to do this better, feel free to do so :-) */
        
        if(preg_match('/<example>/', $paragraph)) {
            // If this paragraph has an example, do some special formatting.
            preg_match('/<title>(.*)<\/title>/', $paragraph, $tmp);
            $functions[$i]['example'] = $tmp[1];
            $functions[$i]['example'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['example']);
            $functions[$i]['example'] = preg_replace('/<.*?>/', '', $functions[$i]['example']);
            $functions[$i]['example'] .= "\n\n";
            preg_match('/<programlisting.*?>(.*)<\/programlisting>/', $paragraph, $tmp);
            $programlisting = $tmp[1];
            // Hmm, no function for this?
            $programlisting = str_replace('&lt;', '<', $programlisting);
            $programlisting = str_replace('&gt;', '>', $programlisting);
            $programlisting = str_replace('&quot;', '"', $programlisting);
            $programlisting = str_replace('&amp;', '&', $programlisting);
            $programlisting = str_replace('&nbsp;', ' ', $programlisting);
            $programlisting = str_replace('&sp;', ' ', $programlisting);
            $functions[$i]['example'] .= `echo '$programlisting' | indent -kr` . "\n\n";
        } elseif(preg_match('/See also/', $paragraph))  {
            $functions[$i]['seealso'] = preg_replace('/<.*?>/', '', $paragraph);
            $functions[$i]['seealso'] = preg_replace('/See also:/', '', $functions[$i]['seealso']);
            $functions[$i]['seealso'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['seealso']);
        } else {
            // Nothing special, just put it in.
            $functions[$i]['paragraph'][$y] = preg_replace('/<.*?>/', '', $paragraph);
            $functions[$i]['paragraph'][$y] = preg_replace('/\s{2,}/', ' ', $functions[$i]['paragraph'][$y]);
        }
        $y++;
    }
   
    $i++;
}

/*
 * We have an array now with all the data, now write it to seperate files.
 */

if(!file_exists('man7')) {
    umask(0000);
    mkdir('man7', 0755);
}

foreach($functions as $function) {
    $fp = fopen("man7/php_" . $function['name'] . ".man",  'w');
    /*
    $function['name']
    $function['shortdesc']
    $function['prototype']
    $function['paragraph'][$y] // Array
    $function['seealso']
    $function['example']
    */

    $page = ".TH " . $function['name'] . " 7  \"" . date("j F, Y") . "\" \"PHPDOC MANPAGE\" \"PHP Programmer's Manual\"\n.SH NAME\n" . 
            $function['name'] . "\n.SH SYNOPSIS\n.B " . $function['prototype'] . "\n.SH DESCRIPTION\n" . $function['shortdesc'] . ".\n";
    if(!empty($function['paragraph']) && count($function['paragraph']) > 0) {
        foreach($function['paragraph'] as $para) {
            $page .= ".PP\n";
            $page .= trim($para) . "\n";
        }
    }

    if(!empty($function['example'])) {
        $page .= ".SH \"EXAMPLE\"\n";
        $page .= $function['example'] . "\n";
    }

    if(!empty($function['seealso'])) {
        $page .= ".SH \"SEE ALSO\"\n";
        $page .= $function['seealso'] . "\n";
    }
    fwrite($fp, $page);
}

/*

source of fopen() manpage as reference:

.\" Copyright (c) 1990, 1991 The Regents of the University of California.
.\" All rights reserved.
.\"
.\" This code is derived from software contributed to Berkeley by
.\" Chris Torek and the American National Standards Committee X3,
.\" on Information Processing Systems.
.\"
.\" Redistribution and use in source and binary forms, with or without
.\" modification, are permitted provided that the following conditions
.\" are met:
.\" 1. Redistributions of source code must retain the above copyright
.\"    notice, this list of conditions and the following disclaimer.
.\" 2. Redistributions in binary form must reproduce the above copyright
.\"    notice, this list of conditions and the following disclaimer in the
.\"    documentation and/or other materials provided with the distribution.
.\" 3. All advertising materials mentioning features or use of this software
.\"    must display the following acknowledgement:
.\"	This product includes software developed by the University of
.\"	California, Berkeley and its contributors.
.\" 4. Neither the name of the University nor the names of its contributors
.\"    may be used to endorse or promote products derived from this software
.\"    without specific prior written permission.
.\"
.\" THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
.\" ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
.\" IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
.\" ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
.\" FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
.\" DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
.\" OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
.\" HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
.\" LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
.\" OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
.\" SUCH DAMAGE.
.\"
.\"     @(#)fopen.3	6.8 (Berkeley) 6/29/91
.\"
.\" Converted for Linux, Mon Nov 29 15:22:01 1993, faith@cs.unc.edu
.\" Modified, aeb, 960421, 970806
.\"
.TH FOPEN 3  "13 December 1995" "BSD MANPAGE" "Linux Programmer's Manual"
.SH NAME
fopen, fdopen, freopen \- stream open functions
.SH SYNOPSIS
.B #include <stdio.h>
.sp
.BI "FILE *fopen (const char *" path ", const char *" mode );
.br
.BI "FILE *fdopen (int " fildes ", const char *" mode );
.br
.BI "FILE *freopen (const char *" path ", const char *" mode ", FILE *" stream );
.SH DESCRIPTION
The
.B fopen
function opens the file whose name is the string pointed to by
.I path
and associates a stream with it.
.PP
The argument
.I mode
points to a string beginning with one of the following sequences
(Additional characters may follow these sequences.):
.TP
.B r
Open text file for reading.  The stream is positioned at the beginning of
the file.
.TP
.B r+
Open for reading and writing.  The stream is positioned at the beginning of
the file.
.TP
.B w
Truncate file to zero length or create text file for writing.  The stream
is positioned at the beginning of the file.
.TP
.B w+
Open for reading and writing.  The file is created if it does not exist,
otherwise it is truncated.  The stream is positioned at the beginning of
the file.
.TP
.B a
Open for writing.  The file is created if it does not exist.  The stream is
positioned at the end of the file.
.TP
.B a+
Open for reading and writing.  The file is created if it does not exist.
The stream is positioned at the end of the file.
.PP
The
.I mode
string can also include the letter ``b'' either as a last character or as
a character between the characters in any of the two-character strings
described above.  This is strictly for compatibility with ANSI X3.159-1989
(``ANSI C'') and has no effect; the ``b'' is ignored on all POSIX
conforming systems, including Linux.
(Other systems may treat text files and binary files differently,
and adding the ``b'' may be a good idea if you do I/O to a binary
file and expect that your program may be ported to non-Unix
environments.)
.PP
Any created files will have mode
.BR S_IRUSR \&| S_IWUSR \&|  S_IRGRP \&|  S_IWGRP \&| S_IROTH \&| S_IWOTH
(0666), as modified by the process' umask value (see
.BR umask (2).
.PP
Reads and writes may be intermixed on read/write streams in any order.
Note that ANSI C requires that a file positioning function intervene
between output and input, unless an input operation encounters end-of-file.
(If this condition is not met, then a read is allowed to return the
result of writes other than the most recent.)
Therefore it is good practice (and indeed sometimes necessary
under Linux) to put an
.B fseek
or
.B fgetpos
operation between write and read operations on such a stream.  This
operation may be an apparent no-op (as in \fIfseek(..., 0L,
SEEK_CUR)\fR called for its synchronizing side effect.
.PP
The
.B fdopen
function associates a stream with the existing file descriptor,
.IR fildes .
The
.I mode
of the stream (one of the values "r", "r+", "w", "w+", "a", "a+")
must be compatible with the mode of the file descriptor.
The file position indicator of the new stream is set to that
belonging to
.IR fildes ,
and the error and end-of-file indicators are cleared.
Modes "w" or "w+" do not cause truncation of the file.
The file descriptor is not dup'ed, and will be closed when
the stream created by
.B fdopen
is closed.
The result of applying
.B fdopen
to a shared memory object is undefined.
.PP
The
.B freopen
function opens the file whose name is the string pointed to by
.I path
and associates the stream pointed to by
.I stream
with it.  The original stream (if it exists) is closed.  The
.I mode
argument is used just as in the
.B fopen
function.  The primary use of the
.B freopen
function is to change the file associated with a standard text stream
.IR "" ( stderr ", " stdin ", or " stdout ).
.SH "RETURN VALUES"
Upon successful completion
.BR fopen ,
.B fdopen
and
.B freopen
return a
.B FILE
pointer.  Otherwise,
.B NULL
is returned and the global variable
.I errno
is set to indicate the error.
.SH ERRORS
.TP
.B EINVAL
The
.I mode
provided to
.BR fopen ,
.BR fdopen ,
or
.B freopen
was invalid.
.PP
The
.BR fopen ,
.B fdopen
and
.B freopen
functions may also fail and set
.I errno
for any of the errors specified for the routine
.BR malloc (3).
.PP
The
.B fopen
function may also fail and set
.I errno
for any of the errors specified for the routine
.BR open (2).
.PP
The
.B fdopen
function may also fail and set
.I errno
for any of the errors specified for the routine
.BR fcntl (2).
.PP
The
.B freopen
function may also fail and set
.I errno
for any of the errors specified for the routines
.BR open (2),
.BR fclose (3)
and
.BR fflush (3).
.SH "SEE ALSO"
.BR open "(2), " fclose (3)
.SH STANDARDS
The
.B fopen
and
.B freopen
functions conform to ANSI X3.159-1989 (``ANSI C'').  The
.B fdopen
function conforms to IEEE Std1003.1-1988 (``POSIX.1'').

*/


?>
