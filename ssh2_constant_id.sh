#! /bin/bash

\grep -lrE --include='*.xml' '"constant\.ssh2-pollhub"' | xargs -d '\n' sed -i 's/"constant\.ssh2-pollhub"/"constant\.ssh2-pollhup"/g'
