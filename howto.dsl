<!DOCTYPE style-sheet PUBLIC "-//James Clark//DTD DSSSL Style Sheet//EN" [
<!ENTITY docbook.dsl SYSTEM "/usr/lib/sgml/docbook/html/docbook.dsl" CDATA DSSSL>
<!ENTITY common.dsl SYSTEM "common.dsl">
]>

<!--

  $Id$

  This file contains HTML-specific stylesheet customization for
  the SGML HOWTO.

-->

<style-specification id="local-docbook" use="docbook">

(define %html-ext% ".html")

(define (toc-depth nd)
  (if (string=? (gi nd) "ARTICLE")
      2 ; the depth of the top-level TOC
      1 ; the depth of all other TOCs
      ))
&common.dsl;

</style-specification>

<external-specification id="docbook" document="docbook.dsl">
