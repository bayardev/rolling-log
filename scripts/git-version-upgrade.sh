#!/bin/bash

if [[ -z "$1" ]]; then
    echo "ERROR: need VERSION NUMBER as argument" && exit 1;
else
    new_version="$1"
fi

GREEN="\033[1;32m"
RED="\033[1;31m"
YELLOW='\033[1;33m\]'
EOC="\033[0m"

rootdir=$(dirname "$(readlink -f "$0")")"/.."
current_version=$(cat "${rootdir}/VERSION")

# Set new version in VERSION file
echo $new_version > "${rootdir}/VERSION" && echo -e "${GREEN}New Version is ${new_version}${EOC}"

# git commit last changes
current_branch=`git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/\1/'`
commit_comment="New Version ${new_version}"
git commit -am "$commit_comment"
# Set next git tag
cd ${rootdir}
git tag -a "v${new_version}" -m "$commit_comment" $(git log --format="%H" -n 1)
# Push last git tag
git push --tags origin $current_branch