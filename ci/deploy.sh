#!/bin/sh

set -e

yes n | grunt release
git push origin master
git push origin master --tags