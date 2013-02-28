<?php
define("DEBUG", 0);
$BUILDDIR = "http://php.tuxxedo.net/chm";
$TMPDIR = sys_get_temp_dir();
$CHMDIR = "/local/mirrors/phpweb/distributions/manual";

$chminfo = "$BUILDDIR/build.log";
fetch($chminfo, tempnam(sys_get_temp_dir(), "chm"));

$chms = file($chminfo, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
if (!$chms) {
    err("No chm info\n");
    return 1;
}

foreach($chms as $line) {
    list($filename, $hash, $date) = explode("\t", $line);
    $tmpfilename  = "$TMPDIR/$filename.new";
    $realfilename = "$CHMDIR/$filename";
    debug("\n\nWorking on $filename ($tmpfilename => $realfilename)\n");

    $dlsize = fetch("$BUILDDIR/$filename", $tmpfilename);
    if (!$dlsize) {
        $err = error_get_last();
        err($err["message"], $filename);
        continue;
    }
    debug("Fetched $dlsize");

    $realhash = md5_file($tmpfilename);
    if ($realhash != $hash) {
        err("\nMD5 Failed for $filename ($realhash != $hash)\n");
        continue;
    }
    debug("\nMD5 OK");

    debug("Renaming $tmpfilename to $realfilename");
    rename($tmpfilename, $realfilename);
    debug("All done with $filename");
}

$errors = err();
if ($errors) {
    mail("phpdoc@lists.php.net", "Errors copying CHM files", var_export($errors, true), "From: phpdoc@lists.php.net\r\n", "-fnoreply@php.net");
    return 2;
}


function err($error = null) {
    static $errors = array();
    if ($error) {
        $errors[] = $error;
    }
    return $errors;
}
function debug($msg) {
    if (DEBUG) {
        echo $msg, "\n";
    }
}
function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
    static $filesize = null;

    switch($notification_code) {
    case STREAM_NOTIFY_RESOLVE:
    case STREAM_NOTIFY_AUTH_REQUIRED:
    case STREAM_NOTIFY_COMPLETED:
    case STREAM_NOTIFY_FAILURE:
    case STREAM_NOTIFY_AUTH_RESULT:
        /* Ignore */
        break;

    case STREAM_NOTIFY_REDIRECTED:
        debug("Being redirected to: ". $message);
        break;

    case STREAM_NOTIFY_CONNECT:
        debug("Connected...");
        break;

    case STREAM_NOTIFY_FILE_SIZE_IS:
        $filesize = $bytes_max;
        debug("Filesize: ". $filesize);
        break;

    case STREAM_NOTIFY_MIME_TYPE_IS:
        debug("Mime-type: ". $message);
        break;

    case STREAM_NOTIFY_PROGRESS:
        if ($bytes_transferred > 0) {
            if (DEBUG) {
                if (!isset($filesize)) {
                    printf("\rUnknown filesize.. %2d kb done..", $bytes_transferred/1024);
                } else {
                    $length = (int)(($bytes_transferred/$filesize)*100);
                    printf("\r[%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length). ">", $length, ($bytes_transferred/1024), $filesize/1024);
                }
            }
        }
        break;
    }
}

function fetch($filename, $outputname) {
    debug("Fetching $filename");
    $ctx = stream_context_create(null, array("notification" => "stream_notification_callback"));

    $fp = fopen($filename, "r", false, $ctx);
    if (!is_resource($fp)) {
        err("Fetch failed");
        return 0;
    }
    return file_put_contents($outputname, $fp);
}



