<?php

class UserController extends BaseController
{
    public function __construct()
    {
        //$this->beforeFilter('csrf', array('on', 'post'));
    }

    public function showLogin()
    {
        if (strpos($_SERVER['HTTP_HOST'], '.biz') !== false) {
            header('location:https://prepagoplatform.com');
            exit();
        }

        if (Auth::check()) {
            if (Auth::user()->isInstaller == 1) {
                return Redirect::to('prepago_installer');
            }

            return Redirect::to('welcome');
        }

        return View::make('login.login');
    }

    public function login_action()
    {
        $username = Input::get('username');
        $password = Input::get('password');

        $rules = [
            'username' => 'required',
            'password' => 'required',
            ];

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::to('/')->withErrors($validator);
        }

        if (Auth::attempt(['username' => $username, 'password' => $password, 'locked' => 0], true)) {
            UserSignIn::stamp(Auth::user()->id, $_SERVER['REMOTE_ADDR']);

            return Redirect::to('welcome-schemes');
        /*if(Auth::user()->isInstaller == 1){
            return Redirect::intended('prepago_installer');
        }
        return Redirect::intended('welcome');*/
        } else {
            Session::flash('signinerror', 'Username or Password doesn\'t match');
            if ($username == 'wow') {
                Session::flash('signinerror', 'Username or Password doesn\'t match: '.$username.'|'.$password);
            }

            return Redirect::to('/')->withInput();
        }
    }
}
