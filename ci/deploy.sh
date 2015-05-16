#!/bin/sh

set -e

echo 'no' | grunt release
git push origin master
git push origin master --tags