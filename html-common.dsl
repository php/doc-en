;; -*- Scheme -*-
;;
;; $Id$
;;

;; Returns the depth of the auto-generated TOC (table of contents) that
;; should be made at the nd-level
(define (toc-depth nd)
  (if (string=? (gi nd) "BOOK")
      2 ; the depth of the top-level TOC
      1 ; the depth of all other TOCs
      ))

(element REFPURPOSE
  (make sequence
    (literal " -- ")
    (process-children)))

(element FUNCTION
  (let* ((function-name (data (current-node)))
	 (linkend (string-append "function." (string-replace function-name
							     "_" "-")))
	 (target (element-with-id linkend))
	 (parent-gi (gi (parent))))
    (cond
     ;; function names should be plain in FUNCDEF
     ((equal? parent-gi "FUNCDEF")
      (process-children))

     ;; if a valid ID for the target function is not found, or if the
     ;; FUNCTION tag is within the definition of the same function,
     ;; make it bold, add (), but don't make a link
     ((or (node-list-empty? target)
	  (equal? (case-fold-down
		   (data (node-list-first
			  (select-elements
			   (node-list-first
			    (children
			     (select-elements
			      (children
			       (ancestor-member (parent) (list "REFENTRY")))
			      "REFNAMEDIV")))
			   "REFNAME"))))
		  function-name))
      ($bold-seq$
       (make sequence
	     (process-children)
	     (literal "()"))))

     ;; else make a link to the function and add ()
     (else
      (make element gi: "A"
	    attributes: (list
			 (list "HREF" (href-to target)))
	    ($bold-seq$
	     (make sequence
		   (process-children)
		   (literal
		    )
		   (literal "()"))))))))

(element EXAMPLE
  (make sequence
    (make element gi: "TABLE"
	  attributes: (list
		       (list "WIDTH" "100%")
		       (list "BORDER" "0")
		       (list "CELLPADDING" "0")
		       (list "CELLSPACING" "0")
		       (list "CLASS" "EXAMPLE"))
      (make element gi: "TR"
	(make element gi: "TD"
	  ($formal-object$))))))
