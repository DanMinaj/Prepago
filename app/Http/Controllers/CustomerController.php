<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DistrictHeatingUsage;
use App\Models\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;


class CustomerController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function edit_dhu($dhu_id)
    {
        $dhu = DistrictHeatingUsage::where('id', $dhu_id)->first();

        if (! $dhu) {
            die("This district_heating_usage ID $dhu_id does not exist!");
        }

        $customer = Customer::find($dhu->customer_id);

        if (! $customer && ! Auth::user()->isUserTest()) {
            die('This DHU has no customer associated with it, or their account is deleted!');
        }

        if (! Auth::user()->isUserTest() && $customer->scheme_number != Auth::user()->scheme_number) {
            die('You do not have permission to access this page.');
        }

        $date = $dhu->date;

        $this->layout->page = view('home.customer_edit_dhu', [

            'customer' => $customer,
            'date' => $date,
            'dhu' => $dhu,

        ]);
    }

    public function edit_dhu_save($dhu_id)
    {
        try {
            $dhu = DistrictHeatingUsage::where('id', $dhu_id)->first();

            if (! $dhu) {
                throw new Exception("This district_heating_usage ID $dhu_id does not exist!");
            }

            $customer = Customer::find($dhu->customer_id);

            if (! $customer && ! Auth::user()->isUserTest()) {
                throw new Exception('This DHU has no customer associated with it, or their account is deleted!');
            }

            if (! Auth::user()->isUserTest() && $customer->scheme_number != Auth::user()->scheme_number) {
                throw new Exception('You do not have permission to access this page.');
            }

            foreach (Input::all() as $key => $value) {
                $dhu->$key = $value;
            }

            $dhu->save();

            return Redirect::back()->with([
                'successMessage' => "Successfully saved changes to DHU #$dhu_id!",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function edit_dhu_spread_cost($dhu_id)
    {
        try {
            $dhu = DistrictHeatingUsage::where('id', $dhu_id)->first();

            if (! $dhu) {
                throw new Exception("This district_heating_usage ID $dhu_id does not exist!");
            }

            $customer = Customer::find($dhu->customer_id);

            if (! $customer && ! Auth::user()->isUserTest()) {
                throw new Exception('This DHU has no customer associated with it, or their account is deleted!');
            }

            if (! Auth::user()->isUserTest() && $customer->scheme_number != Auth::user()->scheme_number) {
                throw new Exception('You do not have permission to access this page.');
            }

            $days = Input::get('days');

            $affected_dhu = $dhu->spreadCharges($days);

            return Redirect::back()->with([
                'successMessage' => "Successfully spread cost over $days days for DHU #$dhu_id!",
                'affected_dhu' => $affected_dhu,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function mass_spread_cost()
    {
        $schemes = Scheme::where('archived', 0)->where('status_debug', 0)->orderBy('scheme_number', 'DESC')->get();

        foreach ($schemes as $key => $val) {
            if (! $val->SIM || $val->SIM == null) {
                $schemes->forget($key);
            }
            if ($val->scheme_number == 15 || $val->scheme_number == 23) {
                $schemes->forget($key);
            }
        }

        $this->layout->page = view('home.mass_spread_cost', [
            'schemes' => $schemes,
        ]);
    }

    public function mass_spread_cost_check()
    {
        try {
            $scheme = Scheme::find(Input::get('scheme_id'));
            $date = Input::get('date');
            $num_days = Input::get('num_days');
            $threshold_amnt = Input::get('threshold_amnt');

            $dhus = DistrictHeatingUsage::where('scheme_number', $scheme->scheme_number)
            ->where('date', $date)
            ->where('cost_of_day', '>=', $threshold_amnt)
            ->get();

            foreach ($dhus as $d) {
                $d->customer = Customer::find($d->customer_id);
                $d->prev = $d->prev;
            }

            return Response::json([
                'scheme' => $scheme->company_name,
                'success' => true,
                'dhus' => $dhus,
                'error' => false,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }

    public function mass_spread_cost_submit()
    {
        try {
            $scheme = Scheme::find(Input::get('scheme_id'));
            $date = Input::get('date');
            $num_days = Input::get('num_days');
            $threshold_amnt = Input::get('threshold_amnt');

            $dhus = DistrictHeatingUsage::where('scheme_number', $scheme->scheme_number)
            ->where('date', $date)
            ->where('cost_of_day', '>=', $threshold_amnt)
            ->get();

            foreach ($dhus as $k => $d) {

                /*
                if($k != 0)
                    continue;
                */

                $d->customer = Customer::find($d->customer_id);
                $d->prev = $d->prev;
                $sffected = $d->spreadCharges($num_days);
            }

            return Response::json([
                'scheme' => $scheme->company_name,
                'success' => true,
                'error' => false,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }
}
