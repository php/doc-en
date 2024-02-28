#! /bin/bash

# typo
\grep -lrE --include='*.xml' '"pdo\.constants\.sqlite_deterministic"' | xargs -d '\n' sed -i 's/"pdo\.constants\.sqlite_deterministic"/"pdo\.constants\.sqlite-deterministic"/g'

# missing 'ssl'
\grep -lrE --include='*.xml' '"pdo\.constants\.mysql-attr-cipher"' | xargs -d '\n' sed -i 's/"pdo\.constants\.mysql-attr-cipher"/"pdo\.constants\.mysql-attr-ssl-cipher"/g'
\grep -lrE --include='*.xml' '"pdo\.constants\.mysql-attr-key"' | xargs -d '\n' sed -i 's/"pdo\.constants\.mysql-attr-key"/"pdo\.constants\.mysql-attr-ssl-key"/g'

# wrong format (non-class constant)
\grep -lrE --include='*.xml' '"pdo-odbc\.constants\.pdo-odbc-type"' | xargs -d '\n' sed -i 's/"pdo-odbc\.constants\.pdo-odbc-type"/"constant\.pdo-odbc-type"/g'
