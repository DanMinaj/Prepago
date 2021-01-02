<?php

namespace App\Libraries\validation;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;



class BaseValidator
{
    protected $passes;

    protected $errors;

    public function __construct()
    {
        $errors = new MessageBag();

        if ($old = Input::old('errors')) {
            $errors = $old;
        }

        $this->errors = $errors;
    }

    public function isValid($rules, $customAttributeNames = [], $customMsgs = [], $dataToValidate = null)
    {
        if (! $dataToValidate) {
            $dataToValidate = Input::all();
        }

        $validator = Validator::make($dataToValidate, $rules, $customMsgs);
        $validator->setAttributeNames($customAttributeNames);

        $this->passes = $validator->passes();
        $this->errors = $validator->errors();
        //$this->errors = $validator->getMessageBag()->toArray();

        return $this->passes;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function hasErrors()
    {
        return $this->errors->any();
    }

    public function getError($key)
    {
        return $this->getErrors()->first($key);
    }

    public function getErrorMarkup($key)
    {
        if ($error = $this->getError($key)) {
            return "<div class='error'>".$error.'</div>';
        }

        return '';
    }

    public function isPosted()
    {
        return Input::server('REQUEST_METHOD') == 'POST';
    }

    public function isAjax()
    {
        return Request::ajax();
    }
}
