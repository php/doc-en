;; -*- Scheme -*-
;;
;; $Id$
;;

;; Returns the depth of the auto-generated TOC (table of
;; contents) that should be made at the nd-level
(define (toc-depth nd)
  (if (string=? (gi nd) "book")
      2 ; the depth of the top-level TOC
      1 ; the depth of all other TOCs
      ))

;; re-defining element-id as we need to get the id of the parent
;; element not only for title but also for question in the faq
(define (element-id #!optional (nd (current-node)))
  (let ((elem (if (equal? (gi nd) (normalize "title")) (parent nd)  
                   (if (equal? (gi nd) (normalize "question")) (parent nd) 
                       nd))))
    (if (attribute-string (normalize "id") elem)
        (attribute-string (normalize "id") elem)
        (generate-anchor elem))))

;; Make function definitions bold
(element (funcdef function) 
  ($bold-seq$
   (make sequence
     (process-children)
     )
   )
  )


;; There are two different kinds of optionals
;; optional parameters and optional parameter parts.
;; An optional parameter is identified by an optional tag
;; with a parameter tag as its parent 
;; and only whitespace between them
(element optional 
  ;;check for true optional parameter
  (if (is-true-optional (current-node))
      ;; yes - handle '[...]' in paramdef
      (process-children-trim) 
      ;; no - do '[...]' output
      (make sequence
        (literal %arg-choice-opt-open-str%)
        (process-children-trim)
        (literal %arg-choice-opt-close-str%)
        )
      )
  )                

;; Print out parameters in italic
(element (paramdef parameter)
  (make sequence
    font-posture: 'italic                                                       
    (process-children-trim)
    )
  )                                                       

;; Now this is going to be tricky
(element paramdef  
  (make sequence
    ;; special treatment for first parameter in funcsynopsis
    (if (equal? (child-number (current-node)) 1)
        ;; is first ?
        (make sequence
          ;; start parameter list
          (literal " (") 
          ;; is optional ?
          ( if (has-true-optional (current-node))
               (literal %arg-choice-opt-open-str%)
               (empty-sosofo)
               )
          )
        ;; not first
        (empty-sosofo)
        )
    
    ;;
    (process-children-trim)
    
    ;; special treatment for last parameter 
    (if (equal? (gi (ifollow (current-node))) (normalize "paramdef"))                                        
        ;; more parameters will follow
        (make sequence
          ;; next is optional ?
          ( if (has-true-optional (ifollow (current-node)))
               ;; optional
               (make sequence
                 (literal " ")
                 (literal %arg-choice-opt-open-str%)
                 )
               ;; not optional
               (empty-sosofo)
               )
          (literal ", " ) 
          )
        ;; last parameter
        (make sequence
          (literal 
           (let loop ((result "")(count (count-true-optionals (parent (current-node)))))
             (if (<= count 0)
                 result
                 (loop (string-append result %arg-choice-opt-close-str%)(- count 1))
                 )
             )
           )
          ( literal ")" )
          )
        )
    )
  )


;; How to print out void in a funcprototype
(element (funcprototype void)
 (make sequence ( literal " (void)" )))

;; How to print out varargs in a funcprototype
(element (funcprototype varargs)
 (make sequence ( literal " (...)" )))


;; Linking types to the correct place
(element type
  (let* 
    ((orig-name (data (current-node)))
      (type-name (cond 
        ((equal-ci? orig-name "bool")   "boolean")
        ((equal-ci? orig-name "double") "float")
        ((equal-ci? orig-name "int")   "integer")
        ((equal-ci? orig-name "NULL")   "null")
        (else orig-name))
      )
      (linkend (string-append "language.types." type-name))
      (target (element-with-id linkend))
    )
    (cond ((node-list-empty? target)
      (make sequence (process-children) )
      )
      (else 
        (make element gi: "A"
          attributes: (list (list "HREF" (href-to target)))
          ( $bold-seq$(make sequence (process-children) ) )
        )
      )
    )
  )
)

;; Linking of function tags
(element function
  (let* ((function-name (data (current-node)))
     (linkend 
      (string-append
       "function." 
       (case-fold-down (string-replace
                        (string-replace function-name "_" "-")
                        "::" "."))))
     (target (element-with-id linkend))
     (parent-gi (gi (parent))))
    (cond
     ;; function names should be plain in FUNCDEF
     ((equal? parent-gi "funcdef")
      (process-children))
     
     ;; If a valid ID for the target function is not found, or if the
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
                   (ancestor-member (parent) (list "refentry")))
                  "refnamediv")))
               "refname"))))
          (case-fold-down function-name)))
      ($bold-seq$
       (make sequence
     (process-children)
     (literal "()"))))
     
     ;; Else make a link to the function and add ()
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


;; Link for classnames
(element classname
  (let* ((class-name (data (current-node)))
     (linkend 
      (string-append
       "class." 
        (string-replace
         (case-fold-down class-name) "_" "-")))
     (target (element-with-id linkend))
     (parent-gi (gi (parent))))
    (cond
     ;; Function names should be plain in SYNOPSIS
     ((equal? parent-gi "synopsis")
      (process-children))
     
     ;; If a valid ID for the target class is not found, or if the
     ;; CLASSNAME tag is within the definition of the same class,
     ;; make it bold, but don't make a link
     ((or (node-list-empty? target)
      (equal? (case-fold-down
           (data (node-list-first
              (select-elements
               (node-list-first
                (children
                 (select-elements
                  (children
                   (ancestor-member (parent) (list "refentry")))
                  "refnamediv")))
               "refname"))))
          class-name))
      ($bold-seq$
       (process-children)))
     
     ;; Else make a link to the class
     (else
      (make element gi: "A"
        attributes: (list
             (list "HREF" (href-to target)))
        ($bold-seq$
         (process-children)))))))


;; Linking to constants
(element constant
  (let* ((constant-name (data (current-node)))
     (linkend 
      (string-append "constant." 
             (case-fold-down
              (string-replace constant-name "_" "-"))))
     (target (element-with-id linkend))
     (parent-gi (gi (parent))))

    (cond
     ;; If a valid ID for the target constant is not found
     ;; make it bold, but don't make a link
     ((or (node-list-empty? target)(attribute-string (normalize "id")(current-node)))
			($bold-mono-seq$
       (process-children)))
     
     ;; Else make a link to the constant
     (else
      (make element gi: "A"
        attributes: (list
             (list "HREF" (href-to target)))
        ($bold-mono-seq$
         (process-children)))))))


;; Dispaly of examples
(element example
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


;; Prosessing tasks for the frontpage
(mode book-titlepage-recto-mode
  (element authorgroup
    (process-children))
    
  (element author
    (let ((author-name  (author-string))
          (author-affil (select-elements (children (current-node)) 
                                         (normalize "affiliation"))))
      (make sequence      
        (make element gi: "DIV"
              attributes: (list (list "CLASS" (gi)))
              (literal author-name))
        (process-node-list author-affil))))

  (element editor
    (let ((editor-name (author-string)))
      (make sequence
        (if (first-sibling?)
            (make element gi: "H2"
                  attributes: (list (list "CLASS" "EDITEDBY"))
                  (literal (gentext-edited-by)))
            (empty-sosofo))
        (make element gi: "DIV"
              attributes: (list (list "CLASS" (gi)))
              (literal editor-name)))))
)


;; Put version info where the refname part in the refnamediv is
(element (refnamediv refname)
  (make sequence
   (if (node-list-empty? 
     (select-elements (children
       (select-elements (children (parent (parent (current-node)))) (normalize "refsect1"))
      ) (normalize "methodsynopsis"))
    )
    (empty-sosofo)
    (make element gi: "P"
      (literal "    (")
      (version-info (current-node))
      (literal ")")
      )
    )
    (process-children)
    )
  )


;; Display of question tags, link targets
(element question
  (let* ((chlist   (children (current-node)))
         (firstch  (node-list-first chlist))
         (restch   (node-list-rest chlist)))
    (make element gi: "B"
    (make element gi: "DIV"
          attributes: (list (list "CLASS" (gi)))
          (make element gi: "P"
                (make element gi: "A"
                      attributes: (list (list "NAME" (element-id)))
                      (empty-sosofo))
                (make element gi: "B"
                      (literal (question-answer-label (current-node)) " "))
                (process-node-list (children firstch)))
          (process-node-list restch))))   )          

;; Adding class HTML parameter to examples
;; having a role parameter, to make PHP examples
;; distinguisable from other ones in the manual
(define ($verbatim-display$ indent line-numbers?)
  (let (
(content (make element gi: "PRE"
       attributes: (list
    (list "CLASS" (if (attribute-string (normalize "role"))
      (attribute-string (normalize "role"))
      (gi))))
       (if (or indent line-numbers?)
   ($verbatim-line-by-line$ indent line-numbers?)
   (process-children-trim)))))
    (if %shade-verbatim%
(make element gi: "TABLE"
      attributes: (list 
                   (list "BORDER" "0")
                   (list "BGCOLOR" "#E0E0E0")
                   (list "CELLPADDING" "5")
                   )
      (make element gi: "TR"
    (make element gi: "TD"
  content)))
(make sequence
  (para-check)
  content
  (para-check 'restart)))))

;; Special handling of note role="seealso"
(define ($admonpara$)
  (let* ((title     (select-elements 
		     (children (parent (current-node))) (normalize "title")))
	 (has-title (not (node-list-empty? title)))
	 (adm-title (if has-title 
			(make sequence
			  (with-mode title-sosofo-mode
			    (process-node-list (node-list-first title)))
			  (literal (gentext-label-title-sep 
				    (gi (parent (current-node))))))
			(literal
			 (gentext-element-name 
			  (if (equal? (normalize "seealso") (attribute-string (normalize "role") (parent (current-node))))
			   (normalize "seealsoie")
			   (parent (current-node))))
			 (gentext-label-title-sep 
			  (gi (parent (current-node))))))))
    (make element gi: "P"
	  (if (and (not %admon-graphics%) (= (child-number) 1))
	      (make element gi: "B"
		    adm-title)
	      (empty-sosofo))
	  (process-children))))



(define (linebreak) (make element gi: "BR" (empty-sosofo)))


;; vim: ts=2 sw=2 et
