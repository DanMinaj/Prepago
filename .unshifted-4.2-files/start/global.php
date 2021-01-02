<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories([

    app_path().'/commands',
    app_path().'/commands/Reminders',
    app_path().'/commands/Management',
    app_path().'/commands/Valve Tasks',
    app_path().'/commands/Backup Tasks',
    app_path().'/commands/Daily Tasks',
    app_path().'/commands/Monthly Tasks',
    app_path().'/commands/Hourly Tasks',
    app_path().'/commands/End of day Tasks',
    app_path().'/commands/Disabled',
    app_path().'/controllers',
    app_path().'/models',
    app_path().'/models/Stripe',
    app_path().'/models/SOAP',
    app_path().'/models/SOAP/res',
    app_path().'/database/seeds',
    app_path().'/libraries/validation',
    app_path().'/repositories',
    app_path().'/controllers/Reports',
    app_path().'/controllers/CSV',
    app_path().'/controllers/SOAP',
    app_path().'/ev',

]);

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a rotating log file setup which creates a new file each day.
|
*/

$logFile = 'log-'.php_sapi_name().'.txt';

Log::useDailyFiles(storage_path().'/logs/'.$logFile);

App::bind('WebServiceRepositoryInterface', function () {
    return new WebServiceRepository;
});

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function (Exception $exception, $code) {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = 'prepago-admin.biz';
    }

    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        $uri = 'undefined';
    }

    if ($code == 404) {
        $msg = "$ip tried to navigate to a non existent page: $uri";
        //$msg .= "\n";
        //$msg .= $exception;
        Log::error($msg);

        return Redirect::to('404')->with([
            'page' => $uri,
        ]);
    }

    if (! Auth::check()) {
        if ($code = 405) {
            $msg = "$ip tried to use a method on page: $uri";
            //$msg .= "\n";
            //$msg .= $exception;
            Log::error($msg);

            return Redirect::to('404')->with([
                'page' => $uri,
            ]);
        }
    }

    $msg = "$ip sent a request to $uri. Page threw an error.";
    $msg .= "\n";
    $msg .= $exception;
    Log::error($msg);
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenace mode is in effect for this application.
|
*/

App::down(function () {
    return Response::make('Be right back!', 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';
require app_path().'/libraries/validation/ValidationExtensions.php';
