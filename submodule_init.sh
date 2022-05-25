#!/bin/sh
set -e
PWD="$(pwd)/"
git config -f .gitmodules --get-regexp '^submodule\..*\.path$' |
    while read path_key path
    do
        url_key=$(echo $path_key | sed 's/\.path/.url/')
        url=$(git config -f .gitmodules --get "$url_key")
        folder="$PWD$path"
        if [ -d "$folder" ]; then
            echo "-- Folder: $folder exists."
        else
            echo "-- Folder: $folder not exists."
            git submodule add $url $path
        fi
    done

git submodule--helper list
