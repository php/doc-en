#! /bin/bash

\grep -lrE --include='*.xml' '"parle\.constant\.utf32"' | xargs -d '\n' sed -i 's/"parle\.constant\.utf32"/"constant\.parle-internal-utf32"/g'
\grep -lrE --include='*.xml' '"parle(.)+\.constants\.flag-regex-' | xargs -d '\n' sed -i 's/\.constants\.flag-regex-/\.constants\./g'
