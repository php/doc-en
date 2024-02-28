#! /bin/bash

\grep -lrE --include='*.xml' '"constant\.apcu-' | xargs -d '\n' sed -i 's/"constant\.apcu-/"constant\.apc-/g'
