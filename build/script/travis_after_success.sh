#!/usr/bin/env bash
set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

## coverage reports
if [[ $RUN_WITH_COVERAGE == 'true' ]]; then php vendor/bin/codacycoverage clover build/logs/clover.xml ; fi
if [[ $RUN_WITH_COVERAGE == 'true' && "$TRAVIS_PULL_REQUEST" == "false" ]]; then ./cc-test-reporter after-build -t=clover build/logs/clover.xml --exit-code $TRAVIS_TEST_RESULT ; fi
if [[ $RUN_WITH_COVERAGE == 'true' && "$TRAVIS_PULL_REQUEST" == "false" && $(phpenv version-name) = "7.2" ]]; then bash <(curl -s https://codecov.io/bash) ; fi
