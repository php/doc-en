#! /bin/bash

\grep -lrE --include='*.xml' --include='*.ent' 'PHP_INI_\*' | xargs -d '\n' sed -i 's/PHP_INI_\*/INI_\*/g'

\grep -lrE --include='*.xml' '<entry>\s*PHP_INI_USER\s*<\/entry>' | xargs -d '\n' sed -i 's/<entry>\s*PHP_INI_USER\s*<\/entry>/<entry><constant>INI_USER<\/constant><\/entry>/g'
\grep -lrE --include='*.xml' '<entry>\s*PHP_INI_PERDIR\s*<\/entry>' | xargs -d '\n' sed -i 's/<entry>\s*PHP_INI_PERDIR\s*<\/entry>/<entry><constant>INI_PERDIR<\/constant><\/entry>/g'
\grep -lrE --include='*.xml' '<entry>\s*PHP_INI_SYSTEM\s*<\/entry>' | xargs -d '\n' sed -i 's/<entry>\s*PHP_INI_SYSTEM\s*<\/entry>/<entry><constant>INI_SYSTEM<\/constant><\/entry>/g'
\grep -lrE --include='*.xml' '<entry>\s*PHP_INI_ALL\s*<\/entry>|<entry>>\s*PHP_INI_ALL\s*<\/entry>' | xargs -d '\n' sed -i -E 's/<entry>\s*PHP_INI_ALL\s*<\/entry>|<entry>>\s*PHP_INI_ALL\s*<\/entry>/<entry><constant>INI_ALL<\/constant><\/entry>/g'

\grep -lrE --include='*.xml' '<constant>\s*PHP_INI_USER\s*<\/constant>|<literal>\s*PHP_INI_USER\s*<\/literal>' | xargs -d '\n' sed -i -E 's/<constant>\s*PHP_INI_USER\s*<\/constant>|<literal>\s*PHP_INI_USER\s*<\/literal>/<constant>INI_USER<\/constant>/g'

\grep -lrE --include='*.xml' '<constant>\s*PHP_INI_PERDIR\s*<\/constant>|<literal>\s*PHP_INI_PERDIR\s*<\/literal>' | xargs -d '\n' sed -i -E 's/<constant>\s*PHP_INI_PERDIR\s*<\/constant>|<literal>\s*PHP_INI_PERDIR\s*<\/literal>/<constant>INI_PERDIR<\/constant>/g'

\grep -lrE --include='*.xml' '<constant>\s*PHP_INI_SYSTEM\s*<\/constant>|<literal>\s*PHP_INI_SYSTEM\s*<\/literal>' | xargs -d '\n' sed -i -E 's/<constant>\s*PHP_INI_SYSTEM\s*<\/constant>|<literal>\s*PHP_INI_SYSTEM\s*<\/literal>/<constant>INI_SYSTEM<\/constant>/g'

\grep -lrE --include='*.xml' '<constant>\s*PHP_INI_ALL\s*<\/constant>|<literal>\s*PHP_INI_ALL\s*<\/literal>' | xargs -d '\n' sed -i -E 's/<constant>\s*PHP_INI_ALL\s*<\/constant>|<literal>\s*PHP_INI_ALL\s*<\/literal>/<constant>INI_ALL<\/constant>/g'

\grep -lrE --include='*.xml' 'PHP_INI_SYSTEM\s*\|\s*PHP_INI_PERDIR' | xargs -d '\n' sed -i 's/PHP_INI_SYSTEM\s*[|]\s*PHP_INI_PERDIR/<constant>INI_SYSTEM<\/constant>\|<constant>INI_PERDIR<\/constant>/g'

\grep -lrE --include='*.xml' '<literal>\\INI_ALL<\/literal>' | xargs -d '\n' sed -i 's/<literal>\\INI_ALL<\/literal>/\\<constant>INI_ALL<\/constant>/g'

\grep -lrE --include='*.xml' ' PHP_INI_ALL ' | xargs -d '\n' sed -i 's/ PHP_INI_ALL / <constant>INI_ALL<\/constant> /g'

\grep -lrE --include='*.xml' '<entry>PHP_INI_ALL ' | xargs -d '\n' sed -i 's/<entry>PHP_INI_ALL /<entry><constant>INI_ALL<\/constant> /g'

\grep -lrE --include='*.xml' '<entry>PHP_INI_SYSTEM ' | xargs -d '\n' sed -i 's/<entry>PHP_INI_SYSTEM /<entry><constant>INI_SYSTEM<\/constant> /g'