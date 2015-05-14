# Updates svn branch with lastest

git svn -h
git fetch
git checkout master
git pull origin master
git checkout svnsync
svn update
git merge --no-ff master
git commit
svn commit
git checkout master