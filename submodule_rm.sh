#!/bin/sh
set -e

git config -f .gitmodules --get-regexp '^submodule\..*\.path$' |
    while read path_key path
    do
        url_key=$(echo $path_key | sed 's/\.path/.url/')
        url=$(git config -f .gitmodules --get "$url_key")
		git rm --cached $path -r
        rm -rf .git/modules/$path
        rm -rf $path
    done

