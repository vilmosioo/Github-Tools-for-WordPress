#!/bin/sh

set -e

echo 'Switching to master and setting identity for git'
git checkout master
git config user.name $GIT_NAME
git config user.email $GIT_EMAIL
git remote set-url origin git@github.com:vilmosioo/Github-Tools-for-WordPress.git

echo 'Patching version...'
grunt bump-only:patch

echo 'Running build command'
grunt || { echo 'Client build failed' ; exit 1; }

echo 'Pushing git data to repo...'
grunt bump-commit

exit 0;

