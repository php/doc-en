BEGIN { parse=0; FS="[\"(,]"; }
/^.*function_entry.*$/ { parse=1; }
/^.*shutdown_function_entry.*$/ { parse=0; }
/^.*\(function_entry.*$/ { parse=0; }
/^.*function_entry \*ptr.*$/ { parse=0; }
/NULL.*?NULL.*?NULL/ { parse=0; }
#/^[[:space:]]{0},/ { parse=0; }
/^[[:space:]]{0}/ { parse=0; }
/^[[:space:]]*{/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_FE/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_NAMED_FE/ { if(parse) { print $2; } }
/^[[:space:]]*cybercash_functions/ { if (parse) { print $2; } }
/^[[:space:]]*UODBC_FE/ { if(parse) { print "odbc_"$2; } }
/^<IN_PHP>/ { if(match($2,"^[A-Za-z0-9_]+$")) print $2; }
