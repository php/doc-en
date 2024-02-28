#! /bin/bash

\grep -lrE --include='*.xml' '"ffi-ffi\.constants\.biggest-alignment"' | xargs -d '\n' sed -i 's/"ffi-ffi\.constants\.biggest-alignment"/"ffi\.constants\.--biggest-alignment--"/g'
