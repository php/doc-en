BEGIN { parse=0; FS="[\"(,]"; }
/^.*function_entry.*$/ { parse=1; }
/^.*function_entry.*_class_functions.*$/ { parse=0; }
/^.*function_entry.*OrbitStruct.*$/ { parse=0; }
/^.*function_entry.*OrbitObject.*$/ { parse=0; }
/^.*shutdown_function_entry.*$/ { parse=0; }
/^.*\(function_entry.*$/ { parse=0; }
/^.*function_entry \*ptr.*$/ { parse=0; }
/^.*\(zend_function_entry.*$/ { parse=0; }
/^.*zend_function_entry \*ptr.*$/ { parse=0; }
/NULL.*?NULL.*?NULL/ { parse=0; }
/^[[:space:]]{0}/ { parse=0; }
/^[[:space:]]*{/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_FE/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_FALIAS/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_NAMED_FE/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_STATIC_FE/ { if(parse) { print $3; } }
/^[[:space:]]*ZEND_FE/ { if(parse) { print $2; } }
/^[[:space:]]*ZEND_FALIAS/ { if(parse) { print $2; } }
/^[[:space:]]*ZEND_NAMED_FE/ { if(parse) { print $2; } }
/^[[:space:]]*cybercash_functions/ { if (parse) { print $2; } }
/^[[:space:]]*UODBC_FE/ { if(parse) { print "odbc_"$2; } }
/^<IN_PHP>/ { if(match($2,"^[A-Za-z0-9_]+$")) print $2; }
