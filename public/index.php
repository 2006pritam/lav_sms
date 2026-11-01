<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| PHP 8.1+ uploaded-files compatibility
|--------------------------------------------------------------------------
|
| PHP 8.1 added a "full_path" key to every $_FILES entry. The locked
| symfony/http-foundation (5.2.x) does not know that key, so it mis-parses
| the entry and any form containing a file input (even an empty one, such as
| the logo field on the Settings page) crashes with a 500:
| "createFromBase(): Argument #1 ($file) must be of type UploadedFile, int given".
| Dropping the key restores the shape Symfony expects.
|
*/

foreach ($_FILES as $field => $info) {
    if (is_array($info) && array_key_exists('full_path', $info)) {
        unset($_FILES[$field]['full_path']);
    }
}

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
