#! /bin/bash
# remove xml IDs from core constants page
\grep -lrE --include='*.xml' 'xml:id="constant.e-error"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-error"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-warning"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-warning"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-parse"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-parse"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-notice"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-notice"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-core-error"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-core-error"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-core-warning"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-core-warning"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-compile-error"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-compile-error"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-compile-warning"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-compile-warning"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-user-error"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-user-error"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-user-warning"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-user-warning"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-user-notice"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-user-notice"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-user-deprecated"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-user-deprecated"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-recoverble-error"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-recoverble-error"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-deprecated"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-deprecated"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-all"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-all"//g'
\grep -lrE --include='*.xml' 'xml:id="constant.e-strict"' | xargs -d '\n' sed -i 's/ xml:id="constant\.e-strict"//g'

# change error level IDs to standard format
\grep -lrE --include='*.xml' '"errorfunc\.constants\.errorlevels\.' | xargs -d '\n' sed -i 's/errorfunc\.constants\.errorlevels\./constant\./g'

# fix incorrectly named ID
\grep -lrE --include='*.xml' '"constant\.e-deprecated-error"' | xargs -d '\n' sed -i 's/"constant\.e-deprecated-error"/"constant\.e-deprecated"/g'