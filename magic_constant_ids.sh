#! /bin/bash

# replace <link> with <constant>
\grep -lrE --include='*.xml' '<link linkend="constant\.trait">__TRAIT__<\/link>' | xargs -d '\n' sed -i 's/<link linkend="constant\.trait">__TRAIT__<\/link>/<constant>__TRAIT__<\/constant>/g'

# rename IDs to standard global constant ID format
\grep -lrE --include='*.xml' '"constant\.line"' | xargs -d '\n' sed -i 's/"constant\.line"/"constant\.--line--"/g'
\grep -lrE --include='*.xml' '"constant\.file"' | xargs -d '\n' sed -i 's/"constant\.file"/"constant\.--file--"/g'
\grep -lrE --include='*.xml' '"constant\.dir"' | xargs -d '\n' sed -i 's/"constant\.dir"/"constant\.--dir--"/g'
\grep -lrE --include='*.xml' '"constant\.function"' | xargs -d '\n' sed -i 's/"constant\.function"/"constant\.--function--"/g'
\grep -lrE --include='*.xml' '"constant\.class"' | xargs -d '\n' sed -i 's/"constant\.class"/"constant\.--class--"/g'
\grep -lrE --include='*.xml' '"constant\.trait"' | xargs -d '\n' sed -i 's/"constant\.trait"/"constant\.--trait--"/g'
\grep -lrE --include='*.xml' '"constant\.method"' | xargs -d '\n' sed -i 's/"constant\.method"/"constant\.--method--"/g'
\grep -lrE --include='*.xml' '"constant\.namespace"' | xargs -d '\n' sed -i 's/"constant\.namespace"/"constant\.--namespace--"/g'
