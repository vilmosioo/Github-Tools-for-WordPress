# Updates svn branch with lastest

set -e

echo 'Publishing svnsync'
git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*" # this is necessary to allow the repo to fetch other remote branches
git fetch
git stash
git checkout svnsync
#svn update
git merge --no-ff --no-edit master
git stash pop
git add . # add the modified files from the build process
git commit -m "Updated version references"
#svn commit
git push origin svnsync

echo 'Publishing master'
git checkout master
git push origin master
git push origin master --tags