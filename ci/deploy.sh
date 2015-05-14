# Updates svn branch with lastest

git fetch
git checkout master
git pull origin master
git checkout -b svnsync origin/svnsync
svn update
git merge --no-ff master
git commit
svn commit
git checkout master