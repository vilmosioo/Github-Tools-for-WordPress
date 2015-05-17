#!/bin/sh

set -e

echo 'Switching to master and setting identity for git'
git checkout master
git config user.name $GIT_NAME
git config user.email $GIT_EMAIL
git config credential.helper "store --file=.git/credentials"
git config remote.origin.url https://github.com/vilmosioo/Github-Tools-for-WordPress.git
echo "https://${GITHUB_TOKEN}:@github.com" > .git/credentials

echo 'Configuring SVN'
echo '[global]' > $HOME/.subversion/servers
echo 'store-passwords = yes' > $HOME/.subversion/servers
echo 'store-plaintext-passwords = yes' > $HOME/.subversion/servers

echo 'Patching version...'
grunt bump-only:patch

echo 'Running build command'
grunt || { echo 'Client build failed' ; exit 1; }

echo 'Pushing git data to repo...'
grunt bump-commit

exit 0;

