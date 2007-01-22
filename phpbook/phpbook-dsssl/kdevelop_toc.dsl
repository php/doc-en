<!DOCTYPE style-sheet PUBLIC "-//James Clark//DTD DSSSL Style Sheet//EN" [
<!ENTITY docbook.dsl SYSTEM "./docbook/docbook-dsssl/html/docbook.dsl" CDATA DSSSL>
]>

<!--

  $Id$

  Stylesheet for generating a .toc file for kdevelop.

-->

<style-sheet>
<style-specification id="docbook-php-funcref" use="docbook">
<style-specification-body>

(declare-flow-object-class element
  "UNREGISTERED::James Clark//Flow Object Class::element")

(element book 
		(make element gi: "kdeveloptoc"
					(make sequence
						(make element gi: "title" (literal "PHP"))
						(make element gi: "base" attributes: (list (list "href" "NO")) (empty-sosofo))
						(process-children)
						)
					)
		)

(element part 
  (make element gi: "tocsect1"
				attributes: (list (list "name" (data (node-list-first 
																							(select-elements (children (current-node)) (normalize "title")))))
													(list "url"  (string-append (id (current-node)) ".html"))
													)
				(process-children)
				)
	)

(element chapter
  (make element gi: "tocsect2"
				attributes: (list (list "name" (data (node-list-first 
																							(select-elements (children (current-node)) (normalize "title")))))
													(list "url"  (string-append (id (current-node)) ".html"))
													)
				(process-children)
				)
	)

(element reference
  (make element gi: "tocsect2"
				attributes: (list (list "name" (data (node-list-first (select-elements (children (current-node)) (normalize "title")))))
													(list "url"  (string-append (id (current-node)) ".html"))
													)
				(process-children)
				)
	)

(element appendix
  (make element gi: "tocsect2"
				attributes: (list (list "name" (data (node-list-first 
																							(select-elements (children (current-node)) (normalize "title")))))
													(list "url"  (string-append (id (current-node)) ".html"))
													)
				(process-children)
				)
	)

(element refentry (process-children))
(element refnamediv (process-children))
(element refname
  (make element gi: "tocsect3"
				attributes: (list (list "name"(data (current-node)))
													(list "url" (string-append (id (parent (parent (current-node)))) ".html"))
													)
				)
)



(default (empty-sosofo))

</style-specification-body>
</style-specification>

<external-specification id="docbook" document="docbook.dsl">

</style-sheet>
