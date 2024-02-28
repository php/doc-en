#! /bin/bash

\grep -lrE --include='*.xml' '"gender\.constants\.(.)+"' | xargs -d '\n' sed -i 's/"gender\.constants\./"gender-gender\.constants\./g'
