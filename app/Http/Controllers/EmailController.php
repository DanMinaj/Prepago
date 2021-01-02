<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class EmailController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function email()
    {
        $this->layout->page = View::make('emails.template');
    }

    public function settings()
    {
        $settings = SystemSetting::where('type', 'email')->get();

        $this->layout->page = View::make('settings/email_settings', ['settings' => $settings]);
    }

    public function settings_add()
    {
        $setting = SystemSetting::where('type', 'email')->where('name', Input::get('name'))->first();

        if ($setting) {
            return Redirect::back()->with('errorMessage', 'That setting does already exists.');
        }

        $new_setting = new SystemSetting();
        $new_setting->type = 'email';
        $new_setting->name = Input::get('name');
        $new_setting->value = Input::get('value');
        $new_setting->save();

        return Redirect::back()->with('successMessage', "Successfully created new email setting '".$new_setting->name."'");
    }

    public function settings_remove($id)
    {
        $setting = SystemSetting::where('id', $id)->first();
        $setting_copy_name = $setting->name;

        if ($setting) {
            $setting->delete();
        }

        return Redirect::back()->with('successMessage', "Successfully removed email setting '$setting_copy_name'");
    }

    public function settings_save($id)
    {
        $setting = SystemSetting::where('id', $id)->first();
        $setting_copy_name = $setting->name;

        if (! $setting) {
            return Redirect::back()->with('errorMessage', 'That email setting does not exist.');
        }

        $new_name = Input::get('name');
        $new_value = Input::get('value');

        $setting->name = $new_name;
        $setting->value = $new_value;
        $setting->save();

        return Redirect::back()->with('successMessage', 'Successfully saved settings.');
    }
}
