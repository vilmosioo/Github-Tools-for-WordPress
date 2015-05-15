#!/bin/sh

set -e

grunt release
git push origin master
git push origin master --tags