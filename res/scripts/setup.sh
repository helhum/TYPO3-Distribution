#!/usr/bin/env bash

if [[ ! -f "private/typo3conf/LocalConfiguration.php" ]]
then
    .ddev/commands/web/recreate-project >&2
else
    >&2 echo 'Skipped initial project setup';
fi
