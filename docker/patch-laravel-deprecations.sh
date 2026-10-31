#!/bin/sh
set -eu

file="vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php"
expected='        error_reporting(-1);'
replacement='        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);'

if [ ! -f "$file" ]; then
    echo "Laravel exception bootstrap not found: $file" >&2
    exit 1
fi
if ! grep -Fqx "$expected" "$file"; then
    echo "Unexpected Laravel bootstrap version; refusing to apply compatibility patch." >&2
    exit 1
fi

# Laravel 8.42 resets PHP error_reporting(-1), turning PHP 8.2 deprecations into
# fatal ErrorExceptions. Keep all other error levels enabled.
sed -i "s/error_reporting(-1);/error_reporting(E_ALL \\& ~E_DEPRECATED \\& ~E_USER_DEPRECATED);/" "$file"
grep -Fqx "$replacement" "$file"
