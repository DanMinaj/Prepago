<?php

namespace App\Libraries\validation;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;


class ValidationException extends Exception
{
}

Validator::extend('unique_mobile_phone', function ($attribute, $value) {
    //check if the value passed for the mobile number exists either in the "mobile_number" field or in the "nominated_telephone" field
    if (Customer::where('mobile_number', '=', $value)->orWhere('nominated_telephone', '=', $value)->count()) {
        return false;
    }

    return true;
});

Validator::extend('valid_mobile_phone', function ($attribute, $value) {
    if (strpos($value, '+44') === 0) {
        $regex = "/^\+(353|44)([\pN]){6,25}$/";
    } else {
        $regex = "/^(\+3538)\d{1}[\s]?\d{3}[\s]?\d{4}$/";
    }
    if (preg_match($regex, $value)) {
        return true;
    }

    return false;
});

Validator::extend('after_date', function ($attribute, $value) {
    $tomorrow = strtotime('+1 day', strtotime(date('Y-m-d')));
    if (strtotime($value) >= $tomorrow) {
        return true;
    }

    return false;
});
