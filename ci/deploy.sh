# Updates svn branch with lastest

set -e

git fetch
git checkout master
git pull origin master
git checkout svnsync
svn update
git merge --no-ff master
git commit
svn commit
git push origin svnsync
git checkout master
git push origin master
git push origin master --tags