#!/bin/sh
set -e

cd /docs
rm -rf ./public/*
npm install
./node_modules/.bin/gatsby build --prefix-paths
rm ./public/*.map