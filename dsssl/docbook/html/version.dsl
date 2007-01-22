;; $Id$
;;
;; This file is part of the Modular DocBook Stylesheet distribution.
;; See ../README or http://www.nwalsh.com/docbook/dsssl/
;;

;; If **ANY** change is made to this file, you _MUST_ alter the
;; following definition:

(define (stylesheet-version)
  (string-append
   "Modular DocBook HTML Stylesheet Version "
   ;; Trim off bounding white space.
   (strip "&VERSION;")))
