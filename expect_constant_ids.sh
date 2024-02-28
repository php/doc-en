#! /bin/bash

\grep -lrE --include='*.xml' '"constants\.expect\.(.)+"' | xargs -d '\n' sed -i 's/"constants\.expect\./"constant\./g'
