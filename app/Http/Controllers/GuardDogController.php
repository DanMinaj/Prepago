<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class GuardDogController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $guarddogs = GuardDog::active();

        $this->layout->page = View::make('home.programs.guard_dog', [
            'guarddogs' => $guarddogs,
        ]);
    }

    public function startGuardDog()
    {
        try {
            $customer_id = Input::get('customer');

            $guard_dogs = GuardDog::execute([$customer_id]);

            return Redirect::back()->with([
                'successMessage'	=> 'Successfully started Guard Dog',

            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error: '.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function stopGuardDog()
    {
        try {
            $id = Input::get('gd_id');

            $guarddog = GuardDog::where('id', $id)->first();

            if (! $guarddog) {
                throw new Exception("Guard dog #$id does not exist!");
            }
            $guarddog->completed = 1;
            $guarddog->completed_at = date('Y-m-d H:i:s');
            $guarddog->save();

            return Response::json([
                'message'	=> 'Successfully stopped Guard Dog',
            ]);
        } catch (Exception $e) {
            return Response::json([
                'message' => 'Failed to stop Guard Dog: '.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function getGuardDog()
    {
        try {
            $id = Input::get('gd_id');

            $guarddog = GuardDog::where('id', $id)->first();

            if (! $guarddog) {
                throw new Exception("Guard dog #$id does not exist!");
            }

            return Response::json($guarddog);
        } catch (Exception $e) {
            return Response::json([
                'message' => 'Failed to stop Guard Dog: '.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }
}
