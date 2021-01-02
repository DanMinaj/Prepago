<?php

namespace App\Http\Controllers;

use App\Models\Scheme;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;



class SetupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public $scheme_inputs = [
        'scheme_nickname',
        'company_name',
        'company_address',
        'vat_rate',
        'currency_sign',
        'prefix',
        'street2',
        'town',
        'county',
        'post_code',
        'country',
    ];

    public $sim_inputs = [
        'ICCID',
        'MSISDN',
        'IP_Address',
    ];

    public function __construct()
    {
        $this->middleware('canAccessAdminSettings', ['except' => ['barcode_reports', 'add_account_action']]);
    }

    public function setupChoose()
    {
        $this->layout->page = view('setup.setup_choose', [

        ]);
    }

    public function setupChooseSubmit()
    {
        $option = Input::get('option');

        $scheme_data = (object) [];

        if (strpos($option, 'edit') !== false) {
            $scheme_id = explode('_', $option)[1];
            $scheme_data->mode = 'edit';
            $scheme_data->scheme = Scheme::find($scheme_id);
            $scheme_data->sim = $scheme_data->scheme->SIM;
        }

        if ($option == 'create') {
            $scheme_data->mode = 'create';
            $scheme_data->scheme = (object) [];
            $scheme_data->sim = (object) [];

            foreach ($this->scheme_inputs as $k => $v) {
                $scheme_data->scheme->$v = '';
            }

            foreach ($this->sim_inputs as $k => $v) {
                $scheme_data->sim->$v = '';
            }
        }

        Session::put('scheme_data', $scheme_data);

        return redirect('setup/scheme');
    }

    public function schemeSetup()
    {
        $option = Session::get('setup_option');
        $scheme_data = Session::get('scheme_data');

        if ($option == null && ! (Session::has('scheme_data') && $scheme_data->scheme != null)) {
            return redirect('setup/choose');
        }

        $step1_complete = true;
        $step2_complete = false;
        foreach ($this->scheme_inputs as $k => $v) {
            if (empty($scheme_data->scheme->$v)) {
                $step1_complete = false;
            }
        }

        $this->layout->page = view('setup.setup_scheme', [

            'option' 			=> $option,
            'scheme_data' 		=> $scheme_data,
            'step1_complete' 	=> $step1_complete,
            'step2_complete' 	=> $step2_complete,

        ]);
    }

    public function schemeSetupSubmit()
    {
        try {
            $scheme_data = Session::get('scheme_data');

            foreach ($this->scheme_inputs as $k => $v) {
                $scheme_data->scheme->$v = Input::get($v);
            }

            foreach ($this->sim_inputs as $k => $v) {
                $scheme_data->sim->$v = Input::get($v);
            }

            Session::put('scheme_data', $scheme_data);

            return Redirect::back()->with([
                'successMessage' => 'Saved changes',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
}
