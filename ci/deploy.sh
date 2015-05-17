#!/bin/sh

set -e

git push origin master
git push origin master --tags
grunt release
