# Updates svn branch with lastest

git checkout master
git pull origin master
git checkout svnsync
git svn rebase
git merge --no-ff master
git commit
git svn dcommit
git checkout master