#! /bin/bash

\grep -lrE --include='*.xml' '"constant\.yar-client-opt-packager"' | xargs -d '\n' sed -i 's/"constant\.yar-client-opt-packager"/"constant.yar-opt-packager"/g'
\grep -lrE --include='*.xml' '"constant\.yar-client-opt-timeout"' | xargs -d '\n' sed -i 's/"constant\.yar-client-opt-timeout"/"constant.yar-opt-timeout"/g'
\grep -lrE --include='*.xml' '"constant\.yar-client-opt-connect-timeout"' | xargs -d '\n' sed -i 's/"constant\.yar-client-opt-connect-timeout"/"constant.yar-opt-connect-timeout"/g'
\grep -lrE --include='*.xml' '"constant\.yar-client-opt-header"' | xargs -d '\n' sed -i 's/"constant\.yar-client-opt-header"/"constant.yar-opt-header"/g'
