<?php

// Fix the modelines of xml files. If no modelines were given, it is assumed
// that the xml is two levels deeper than the root of the phpdoc dir, otherwise
// it's copied from the emacs property sgml-default-dtd-file
function apply($input)
{
	$lines = explode("\n",$input);
	$numlines = count($lines);
	$modeline_started = FALSE;
	$manual_ced_line = NULL;
	$output = "";
	foreach ($lines as $nr=>$line) {
		if (eregi("Keep this comment at the end of the file", $line)) {

			// we're on top of the comment
			if ($nr + 20 < $numlines) {
				// there's too much of lines left, bail out
				?>
		ERROR in this file, modelines seems to be not at end of file!
				<?php
				exit;
			}
			
			// break out of for-loop

			$modeline_started = TRUE;

		}

		if ($modeline_started) {
			if (ereg("sgml-default-dtd-file(.*)manual\.ced", $line, $regs)) {
				$manual_ced_line = "sgml-default-dtd-file$regs[1]manual.ced\"";
			}
		}
						
						

		if (!$modeline_started) {
			$output .= "$line\n";
		}
	}

	if (!$modeline_started) {
		echo "WARNING: did NOT found start of modelines!\n";
	}

	if (!$manual_ced_line) {
		echo "WARNING: did NOT found a ced-line!\n";
		$manual_ced_line = 'sgml-default-dtd-file:"../../manual.ced"';
	}

	$output .= <<<HEREDOC
<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil

HEREDOC;
	$output .= "$manual_ced_line\n";
	$output .= <<<HEREDOC
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->

HEREDOC;
	
	return $output;
}
