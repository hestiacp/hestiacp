#!/bin/bash

git clone https://github.com/kristankenney/hestiacp.git
cd hestiacp/src
git checkout development
bash hst_compile.sh -ih development
