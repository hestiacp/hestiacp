#!/bin/bash
# Original code: @jroman00 (https://gist.github.com/mathiasverraes/3096500#gistcomment-1575416)

error=false
current=$1

if [ -z "$current" ]; then
	current="/usr/local/hestia/web/"
fi

if [ ! -d $current ] && [ ! -f $current ]; then
	echo "Invalid directory or file: $current"
	error=true
fi

echo "Checking PHP files..."
for file in $(find $current -type f -name "*.php" -not -path "${current}fm/*"); do
	RESULTS=$(php -l -n $file)

	if [ "$RESULTS" != "No syntax errors detected in $file" ]; then
		echo $RESULTS
		error=true
	fi
done

echo "Checking HTML/PHP combined files..."
for file in $(find $current -type f -name "*.html" -not -path "${current}fm/*"); do
	RESULTS=$(php -l -n $file)

	if [ "$RESULTS" != "No syntax errors detected in $file" ]; then
		echo $RESULTS
		error=true
	fi
done

if [ "$error" = true ]; then
	exit 1
else
	exit 0
fi
