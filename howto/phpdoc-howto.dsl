<!DOCTYPE style-sheet PUBLIC "-//James Clark//DTD DSSSL Style Sheet//EN" [
<!ENTITY docbook.dsl SYSTEM "/usr/lib/sgml/stylesheets/nwalsh-modular/html/docbook.dsl" CDATA DSSSL>
<!ENTITY html-common.dsl SYSTEM "../html-common.dsl">
<!ENTITY html-locale.dsl SYSTEM "../html-locale.dsl">
<!ENTITY common.dsl SYSTEM "../common.dsl">
<!ENTITY version.dsl SYSTEM "../version.dsl">
]>

<!--

  $Id$

  HTML-specific stylesheet customization.

-->

<style-sheet>
<style-specification id="docbook-php-html" use="docbook">
<style-specification-body>

(define %html-ext% ".html")
(define %output-dir% "html")

(define %generate-article-toc%
  ;; Should a Table of Contents be produced for Articles?
  #t)

(define (toc-depth nd)
  2)

(define %section-autolabel%
  ;; Are sections enumerated?
  #t)

&html-common.dsl;
&html-locale.dsl;
&common.dsl;
&version.dsl;

</style-specification-body>
</style-specification>

<external-specification id="docbook" document="docbook.dsl">

</style-sheet>
