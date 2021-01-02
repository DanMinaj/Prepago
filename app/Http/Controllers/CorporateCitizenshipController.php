<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CorporateCitizenship;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CorporateCitizenshipController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function register_interest()
    {
        try {
            $name = Input::get('name');
            $address = Input::get('address');
            $contact = Input::get('contact');
            $focus = Input::get('focus');
            $how_many = Input::get('how_many');
            $hq = Input::get('hq');
            $aim = Input::get('aim');
            $result = Input::get('result');
            $promo_materials = Input::get('promo_materials');
            $promo_run = Input::get('promo_run');

            if (empty($name) || empty($address) || empty($contact) || empty($focus) ||
            empty($how_many) || empty($hq) || empty($aim) || empty($result) ||
            empty($promo_materials) || empty($promo_run)) {
                throw new Exception('Please fill in all required fields.');
            }

            $corporate_citizenship_initiative = new CorporateCitizenship();
            $corporate_citizenship_initiative->name = $name;
            $corporate_citizenship_initiative->address = $address;
            $corporate_citizenship_initiative->contact = $contact;
            $corporate_citizenship_initiative->focus = $focus;
            $corporate_citizenship_initiative->how_many = $how_many;
            $corporate_citizenship_initiative->hq = $hq;
            $corporate_citizenship_initiative->aim = $aim;
            $corporate_citizenship_initiative->result = $result;
            $corporate_citizenship_initiative->promo_materials = $promo_materials;
            $corporate_citizenship_initiative->promo_run = $promo_run;
            $corporate_citizenship_initiative->ip_address = $_SERVER['REMOTE_ADDR'];
            $corporate_citizenship_initiative->reviewed = 0;
            $corporate_citizenship_initiative->save();

            return Response::json([
                'success' => 'Thank you.',
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
                //'error' => $e->getMessage() . " (" . $e->getLine() . ")"
            ]);
        }
    }

    public function slots()
    {
        try {
            $slots = [
                (object) [
                    'dates' 	=> '7 Sept - 28 Sept',
                    'status' 	=> 'Taken',
                ],
                (object) [
                    'dates' 	=> '28 Sept - 26 Oct',
                    'status' 	=> 'Taken',
                ],
                (object) [
                    'dates' 	=> '27 Oct - 14 Nov',
                    'status' 	=> 'Halloween, Diwali',
                ],
                (object) [
                    'dates' 	=> '16 Nov - 14 Dec',
                    'status' 	=> '- Free -',
                ],
                (object) [
                    'dates' 	=> '15 Dec - 5 Jan',
                    'status' 	=> 'Christmas, New Year',
                ],
                (object) [
                    'dates' 	=> '6 Jan - 8 Feb',
                    'status' 	=> 'Dublin Zoo',
                ],
                (object) [
                    'dates' 	=> '9 Feb - 12 Feb',
                    'status' 	=> 'Lunar (Chinese) New Year',
                ],
                (object) [
                    'dates' 	=> '13 Feb - 15 Feb',
                    'status' 	=> 'Valentines day',
                ],
                (object) [
                    'dates' 	=> '16 Feb - 7 Mar',
                    'status' 	=> 'UNICEF',
                ],
                (object) [
                    'dates' 	=> '8 Mar - 29 Mar',
                    'status' 	=> '- Free -',
                ],
                (object) [
                    'dates' 	=> '30 Mar - 4 Apr',
                    'status' 	=> 'Easter',
                ],
                (object) [
                    'dates' 	=> '5 Apr - 25 Apr',
                    'status' 	=> '- Free -',
                ],
                (object) [
                    'dates' 	=> '26 Apr - 8 May',
                    'status' 	=> '- Free -',
                ],
                (object) [
                    'dates' 	=> '9 May - 30 May',
                    'status' 	=> '- Free -',
                ],
            ];

            return Response::json([
                'slots' => $slots,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
                //'error' => $e->getMessage() . " (" . $e->getLine() . ")"
            ]);
        }
    }

    public function activeCampaign()
    {
        try {
            $campaign = Campaign::where('active', 1)->orderBy('id', 'DESC')->first();

            if (! $campaign) {
                $campaign = new Campaign();
                $campaign->icon_img = 'https://hlfppt.org/wp-content/uploads/2017/04/placeholder.png';
                $campaign->title = 'There is no active campaign currently.';
                $campaign->body = '';
                $campaign->teaser = '';
            }

            return Response::json([
                'campaign' => $campaign,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
                //'error' => $e->getMessage() . " (" . $e->getLine() . ")"
            ]);
        }
    }
}
