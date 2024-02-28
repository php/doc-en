#! /bin/bash

\grep -lrE --include='*.xml' '"svm\.constants\.opt-propability"' | xargs -d '\n' sed -i 's/"svm\.constants\.opt-propability"/"svm.constants.opt-probability"/g'
