#!/bin/bash
E_INVALID=3
check_result() { echo "Error $1: $2"; }
is_valid_max_workers() {
    if ! [[ "$1" =~ ^[0-9]+$ ]]; then
        check_result "$E_INVALID" "Invalid Max Workers format :: $1"
    fi
}
is_valid_max_workers 10
is_valid_max_workers '10'
is_valid_max_workers "'10'"
