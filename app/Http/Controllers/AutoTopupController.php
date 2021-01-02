<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class AutoTopupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function autotopup_settings()
    {
        $autotopup_title = SystemSetting::get('autotopup_title');
        $autotopup_subtitle = SystemSetting::get('autotopup_subtitle');
        $autotopup_body = SystemSetting::get('autotopup_body');
        $autotopup_terms = SystemSetting::get('autotopup_terms');
        $vars = SystemSetting::whereRaw("(name LIKE '%vars_autotopup_%')")->get();
        //
        $this->layout->page = View::make('settings.autotopup.settings', [
            'autotopup_title' 		=> $autotopup_title,
            'autotopup_subtitle' 	=> $autotopup_subtitle,
            'autotopup_body' 		=> $autotopup_body,
            'autotopup_terms' 		=> $autotopup_terms,
            'vars' 					=> $vars,
        ]);
    }

    public function autotopup_settings_edit_signup()
    {
        try {
            $title = Input::get('title');
            $subtitle = Input::get('subtitle');
            $body = Input::get('body');

            SystemSetting::modify('autotopup_title', 'value', $title, 'subscription');
            SystemSetting::modify('autotopup_body', 'value', $body, 'subscription');
            SystemSetting::modify('autotopup_subtitle', 'value', $subtitle, 'subscription');

            return Redirect::back()->with([
                'successMessage' => 'Sucessfully saved changes.',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function autotopup_settings_edit_terms()
    {
        try {
            $terms = Input::get('terms');

            SystemSetting::modify('autotopup_terms', 'value', $terms, 'subscription');

            return Redirect::back()->with([
                'successMessage' => 'Sucessfully saved changes.',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function autotopup_settings_edit_vars()
    {
        try {
            $inputs = Input::all();

            foreach ($inputs as $k => $i) {
                $name = $k;
                $value = $i;

                if ($name == 'new_var_name' || $name == 'new_var_value') {
                    continue;
                }

                SystemSetting::modify($name, 'value', $value, 'subscription');
            }

            $new_var_name = Input::get('new_var_name');
            $new_var_value = Input::get('new_var_value');

            if (! empty($new_var_name) && ! empty($new_var_value)) {
                SystemSetting::modify($new_var_name, 'value', $new_var_value, 'subscription');
            }

            //SystemSetting::modify('vars_autotopup_alias', 'value', $terms, 'subscription') ;

            return Redirect::back()->with([
                'successMessage' => 'Sucessfully saved changes.',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function autotopup_settings_create()
    {
        try {
            return Redirect::back()->with([
                'successMessage' => 'Sucessfully saved changes.',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
}
