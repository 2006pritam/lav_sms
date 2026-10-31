#!/bin/sh
set -eu

laravel="vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php"
carbon="vendor/nesbot/carbon/src/Carbon/Traits/Creator.php"

if [ ! -f "$laravel" ]; then
    echo "Laravel exception bootstrap not found: $laravel" >&2
    exit 1
fi
if ! grep -Fqx '        error_reporting(-1);' "$laravel"; then
    echo "Unexpected Laravel bootstrap version; refusing to apply compatibility patch." >&2
    exit 1
fi

# Laravel 8.42 resets PHP error_reporting(-1), turning PHP 8.2 deprecations into
# fatal ErrorExceptions. Keep all other error levels enabled.
sed -i 's/error_reporting(-1);/error_reporting(E_ALL \& ~E_DEPRECATED \& ~E_USER_DEPRECATED);/' "$laravel"
grep -Fqx '        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);' "$laravel"

if [ ! -f "$carbon" ]; then
    echo "Carbon date-creator not found: $carbon" >&2
    exit 1
fi
if ! grep -Fqx '    private static function setLastErrors(array $lastErrors)' "$carbon" || \
   ! grep -Fqx '        static::$lastErrors = $lastErrors;' "$carbon"; then
    echo "Unexpected Carbon version; refusing to apply compatibility patch." >&2
    exit 1
fi

# PHP 8.2 returns false when DateTime has no parse errors; Carbon 2.48 expects
# an array. Normalize that value while preserving the expected result shape.
sed -i \
    -e 's/private static function setLastErrors(array $lastErrors)/private static function setLastErrors($lastErrors)/' \
    -e "s/static::\$lastErrors = \$lastErrors;/static::\$lastErrors = \$lastErrors ?: ['warning_count' => 0, 'warnings' => [], 'error_count' => 0, 'errors' => []];/" \
    "$carbon"
grep -Fqx '    private static function setLastErrors($lastErrors)' "$carbon"
grep -Fqx "        static::\$lastErrors = \$lastErrors ?: ['warning_count' => 0, 'warnings' => [], 'error_count' => 0, 'errors' => []];" "$carbon"
