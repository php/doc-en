#! /bin/bash

\grep -lrE --include='*.xml' '"radius\.constants\.radius-mppe-key-len"' | xargs -d '\n' sed -i 's/"radius\.constants\.radius-mppe-key-len"/"constant.radius-mppe-key-len"/g'
