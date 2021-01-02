<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SimulatorController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function simulate()
    {
        $simulatedScheme = $this->getSimulatedScheme();

        $this->layout->page = view('settings.simulator.simulator', [
            'simulatedScheme' => $simulatedScheme,
        ]);
    }

    public function simulateSubmit()
    {
        try {
            $scheme_to_simulate = Input::get('scheme_to_simulate');
            $currency_sign = Input::get('currency_sign');

            $simulatedScheme = $this->getSimulatedScheme();

            if ($simulatedScheme) {
                $schemeToSimulate = Scheme::find($scheme_to_simulate);

                foreach ($schemeToSimulate->getAttributes() as $k => $v) {
                    if ($k == 'id' || $k == 'scheme_number' || $k == 'simulator') {
                        continue;
                    }
                    {
                        $simulatedScheme->$k = $v;
                        //echo $k . '=>' . $v . '<br/>';
                    }
                }

                if (! empty($currency_sign)) {
                    $simulatedScheme->currency_sign = $currency_sign;
                }

                if ($scheme_to_simulate != $simulatedScheme->scheme_number) {
                    $simulatedScheme->simulator = $scheme_to_simulate;
                    $simulatedScheme->scheme_nickname = $schemeToSimulate->scheme_nickname.' simulator';
                }

                $simulatedScheme->save();
                $simulatedScheme->updateSimulator();

                if (! UserScheme::where('user_id', Auth::user()->id)->where('scheme_id', $simulatedScheme->scheme_number)->first()) {
                    $entry = new UserScheme();
                    $entry->user_id = Auth::user()->id;
                    $entry->scheme_id = $simulatedScheme->scheme_number;
                    $entry->save();
                }
            } else {
                $schemeToSimulate = Scheme::find($scheme_to_simulate);

                $simualtedSchemeId = Scheme::orderBy('scheme_number', 'DESC')->first();
                $simualtedSchemeId = $simualtedSchemeId->scheme_number;

                $simulatedScheme = new Scheme();
                $simulatedScheme->scheme_number = $simualtedSchemeId + 1;

                foreach ($schemeToSimulate->getAttributes() as $k => $v) {
                    if ($k == 'id' || $k == 'scheme_number' || $k == 'simulator') {
                        continue;
                    }
                    {
                        $simulatedScheme->$k = $v;
                        //echo $k . '=>' . $v . '<br/>';
                    }
                }

                if (! empty($currency_sign)) {
                    $simulatedScheme->currency_sign = $currency_sign;
                }
                $simulatedScheme->simulator = $scheme_to_simulate;
                $simulatedScheme->scheme_nickname = $schemeToSimulate->scheme_nickname.' simulator';
                $simulatedScheme->save();
                $simulatedScheme->updateSimulator();

                if (! UserScheme::where('user_id', Auth::user()->id)->where('scheme_id', $simulatedScheme->scheme_number)->first()) {
                    $entry = new UserScheme();
                    $entry->user_id = Auth::user()->id;
                    $entry->scheme_id = $simulatedScheme->scheme_number;
                    $entry->save();
                }

                //$simulatedScheme->scheme_type = $schemeToSimulate->scheme_type;
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully simualted scheme',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    private function getSimulatedScheme()
    {
        $simulatedScheme = Scheme::whereRaw('(simulator != 0)')->first();

        return $simulatedScheme;
    }
}
