;;
;; $Id$	
;;
;; This file contains stylesheet customization to get bookmark levels
;; right in PDF version of manual.
;;

;; borrowed from print/dbsect.dsl

(define ($section-title$)
  (let* ((sect (current-node))
         (info (info-element))
         (exp-children (if (node-list-empty? info)
                           (empty-node-list)
                           (expand-children (children info) 
                                            (list (normalize "bookbiblio") 
                                                  (normalize "bibliomisc")
                                                  (normalize "biblioset")))))
         (parent-titles (select-elements (children sect) (normalize "title")))
         (info-titles   (select-elements exp-children (normalize "title")))
         (titles        (if (node-list-empty? parent-titles)
                            info-titles
                            parent-titles))
         (subtitles     (select-elements exp-children (normalize "subtitle")))
         (renderas (inherited-attribute-string (normalize "renderas") sect))
         (hlevel                          ;; the apparent section level;
          (if renderas                    ;; if not real section level,
              (string->number             ;;   then get the apparent level
               (substring renderas 4 5))  ;;   from "renderas",
              (SECTLEVEL)))               ;; else use the real level
         (hs (HSIZE (- 4 hlevel))))
    (make sequence
      (make paragraph
        font-family-name: %title-font-family%
        font-weight:  (if (< hlevel 5) 'bold 'medium)
        font-posture: (if (< hlevel 5) 'upright 'italic)
        font-size: hs
        line-spacing: (* hs %line-spacing-factor%)
        space-before: (* hs %head-before-factor%)
        space-after: (if (node-list-empty? subtitles)
                         (* hs %head-after-factor%)
                         0pt)
        start-indent: (if (or (>= hlevel 3)
                              (member (gi) (list (normalize "refsynopsisdiv") 
                                                 (normalize "refsect1") 
                                                 (normalize "refsect2") 
                                                 (normalize "refsect3"))))
                          %body-start-indent%
                          0pt)
        first-line-start-indent: 0pt
        quadding: %section-title-quadding%
        keep-with-next?: #t
        heading-level: (if %generate-heading-level% (+ hlevel 2) 0)
        ;; SimpleSects are never AUTO numbered...they aren't hierarchical
        (if (string=? (element-label (current-node)) "")
            (empty-sosofo)
            (literal (element-label (current-node)) 
                     (gentext-label-title-sep (gi sect))))
        (element-title-sosofo (current-node)))
      (with-mode section-title-mode
        (process-node-list subtitles))
      ($section-info$ info))))

;; borrowed from print/dbcompon.dsl

(define ($sub-component$)
  (make simple-page-sequence
    page-n-columns: %page-n-columns%
    page-number-restart?: (or %page-number-restart% 
			      (book-start?) 
			      (first-chapter?))
    page-number-format: ($page-number-format$)
    use: default-text-style
    left-header:   ($left-header$)
    center-header: ($center-header$)
    right-header:  ($right-header$)
    left-footer:   ($left-footer$)
    center-footer: ($center-footer$)
    right-footer:  ($right-footer$)
    start-indent: %body-start-indent%
    input-whitespace-treatment: 'collapse
    quadding: %default-quadding%
    (make sequence
      ($sub-component-title$)
      (process-children))
    (make-endnotes)))

(define ($sub-component-title$)
  (let* ((info (cond
		((equal? (gi) (normalize "appendix"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "article"))
		 (select-elements (children (current-node)) (normalize "artheader")))
		((equal? (gi) (normalize "bibliography"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "chapter"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "dedication")) 
		 (empty-node-list))
		((equal? (gi) (normalize "glossary"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "index"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "preface"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "reference"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "setindex"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		(else
		 (empty-node-list))))
	 (exp-children (if (node-list-empty? info)
			   (empty-node-list)
			   (expand-children (children info) 
					    (list (normalize "bookbiblio") 
						  (normalize "bibliomisc")
						  (normalize "biblioset")))))

         (partintro     (select-elements (children (current-node)) 
					 (normalize "partintro")))
	 (parent-titles (select-elements (children (current-node)) (normalize "title")))
	 (info-titles   (select-elements exp-children (normalize "title")))
	 (titles        (if (node-list-empty? parent-titles)
			    info-titles
			    parent-titles))
	 (subtitles     (select-elements exp-children (normalize "subtitle"))))

    (make sequence
      (make paragraph
	font-family-name: %title-font-family%
	font-weight: 'bold
	font-size: (HSIZE 4)
	line-spacing: (* (HSIZE 4) %line-spacing-factor%)
	space-before: (* (HSIZE 4) %head-before-factor%)
	start-indent: 0pt
	first-line-start-indent: 0pt
	quadding: %component-title-quadding%
	heading-level: (if %generate-heading-level% 2 0)
	keep-with-next?: #t

	(if (string=? (element-label) "")
	    (empty-sosofo)
	    (literal ;; (gentext-element-name-space (current-node))
	     (if (equal? (gi) (normalize "reference")) ""
	             (gentext-element-name-space (current-node)))
		     (element-label)
		     (gentext-label-title-sep (gi))))

	(if (node-list-empty? titles)
	    (element-title-sosofo) ;; get a default!
	    (with-mode component-title-mode
	      (make sequence
		(process-node-list titles)))))

      (make paragraph
	font-family-name: %title-font-family%
	font-weight: 'bold
	font-posture: 'italic
	font-size: (HSIZE 3)
	line-spacing: (* (HSIZE 3) %line-spacing-factor%)
	space-before: (* 0.5 (* (HSIZE 3) %head-before-factor%))
	space-after: (* (HSIZE 4) %head-after-factor%)
	start-indent: 0pt
	first-line-start-indent: 0pt
	quadding: %component-subtitle-quadding%
	keep-with-next?: #t

	(with-mode sub-component-title-mode
	  (make sequence
	    (process-node-list subtitles)))

	(make sequence
	  ($process-partintro$ partintro #t)
	  (empty-sosofo))))))


(mode sub-component-title-mode
  (element title
    (process-children))

  (element subtitle
    (process-children))
)

(define ($component-title$)
  (let* ((info (cond
;; 		((equal? (gi) (normalize "appendix"))
;; 		 (select-elements (children (current-node)) (normalize "docinfo")))
 		((equal? (gi) (normalize "article"))
 		 (select-elements (children (current-node)) (normalize "artheader")))
 		((equal? (gi) (normalize "bibliography"))
 		 (select-elements (children (current-node)) (normalize "docinfo")))
;; 		((equal? (gi) (normalize "chapter"))
;; 		 (select-elements (children (current-node)) (normalize "docinfo")))
 		((equal? (gi) (normalize "dedication")) 
 		 (empty-node-list))
 		((equal? (gi) (normalize "glossary"))
 		 (select-elements (children (current-node)) (normalize "docinfo")))
 		((equal? (gi) (normalize "index"))
 		 (select-elements (children (current-node)) (normalize "docinfo")))
 		((equal? (gi) (normalize "part"))
 		 (select-elements (children (current-node)) (normalize "docinfo")))
 		((equal? (gi) (normalize "preface"))
 		 (select-elements (children (current-node)) (normalize "docinfo")))
;; 		((equal? (gi) (normalize "reference"))
;; 		 (select-elements (children (current-node)) (normalize "docinfo")))
		((equal? (gi) (normalize "setindex"))
		 (select-elements (children (current-node)) (normalize "docinfo")))
		(else
		 (empty-node-list))))
	 (exp-children (if (node-list-empty? info)
			   (empty-node-list)
			   (expand-children (children info) 
					    (list (normalize "bookbiblio") 
						  (normalize "bibliomisc")
						  (normalize "biblioset")))))
	 (parent-titles (select-elements (children (current-node)) (normalize "title")))
	 (info-titles   (select-elements exp-children (normalize "title")))
	 (titles        (if (node-list-empty? parent-titles)
			    info-titles
			    parent-titles))
	 (subtitles     (select-elements exp-children (normalize "subtitle"))))
    (make sequence
      (make paragraph
	font-family-name: %title-font-family%
	font-weight: 'bold
	font-size: (* (HSIZE 4) 1.25)
	line-spacing: (* (HSIZE 4) %line-spacing-factor%)
	space-before: (* (HSIZE 4) %head-before-factor%)
	start-indent: 0pt
	first-line-start-indent: 0pt
	quadding: %component-title-quadding%
	heading-level: (if %generate-heading-level% 1 0)
	keep-with-next?: #f

	(if (string=? (element-label) "")
	    (empty-sosofo)
	    (literal (gentext-element-name-space (current-node))
		     (element-label)
		     (gentext-label-title-sep (gi))))

	(if (node-list-empty? titles)
	    (element-title-sosofo) ;; get a default!
	    (with-mode component-title-mode
	      (make sequence
		(process-node-list titles)))))

      (make paragraph
	font-family-name: %title-font-family%
	font-weight: 'bold
	font-posture: 'italic
	font-size: (HSIZE 3)
	line-spacing: (* (HSIZE 3) %line-spacing-factor%)
	space-before: (* 0.5 (* (HSIZE 3) %head-before-factor%))
	space-after: (* (HSIZE 4) %head-after-factor%)
	start-indent: 0pt
	first-line-start-indent: 0pt
	quadding: %component-subtitle-quadding%
	keep-with-next?: #f

	(with-mode component-title-mode
	  (make sequence
	    (process-node-list subtitles)))))))


;; own code

(element part ($component$))
(element (part title) (empty-sosofo))

(element chapter ($sub-component$))
(element (chapter title) (empty-sosofo))

(element reference ($sub-component$))
(element (reference title) (empty-sosofo))

(element appendix ($sub-component$))
(element (appendix title) (empty-sosofo))

(define %generate-part-titlepage% #t)
(define %generate-chapter-titlepage% #t)
(define %generate-reference-titlepage% #t)
(define %generate-partintro-on-titlepage% #t)