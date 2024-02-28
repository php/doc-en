#! /bin/bash

\grep -lrE --include='*.xml' '"(.)+\.class\.constants\.(.)+"' | xargs -d '\n' sed -i 's/\.class\.constants\./\.constants\./g'
