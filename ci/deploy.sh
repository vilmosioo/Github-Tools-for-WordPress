# Updates svn branch with lastest

git checkout master
git pull origin master
git checkout svnsync
svn update
git merge --no-ff master
git commit
svn dcommit
git checkout master