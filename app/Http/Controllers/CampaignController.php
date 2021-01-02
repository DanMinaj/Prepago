<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as Response;

class CampaignController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $campaigns = Campaign::orderBy('id', 'DESC')->get();

        $this->layout->page = view('home.campaign.index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create()
    {
        try {
            $this->layout->page = view('home.campaign.create', [

            ]);
        } catch (Exception $e) {
        }
    }

    public function create_submit()
    {
        try {
            $from = Input::get('from');
            $to = Input::get('to');
            $title = Input::get('title');
            $teaser = Input::get('teaser');
            $img = Input::get('img');
            $body = Input::get('body');
            $create_notif = (Input::get('create_notif') == 'on') ? true : false;
            $notif_btn_txt = Input::get('notif_btn_txt');

            if ($img == 'default') {
                $img = null;
            }

            if ($create_notif) {
                $notif_btn_txt = 'Read more';
            } else {
                $notif_btn_txt = 'n/a';
            }

            $campaign = new Campaign();
            $campaign->title = $title;
            $campaign->teaser = $teaser;
            $campaign->icon_img = $img;
            $campaign->body = $body;
            $campaign->notif_button_body = $teaser;
            $campaign->notif_button_txt = $notif_btn_txt;
            $campaign->show_from = $from;
            $campaign->show_to = $to;
            $campaign->seen_by = 0;
            $campaign->interact_seen_by = serialize([]);
            $campaign->active = 0;
            $campaign->save();

            return redirect('campaigns')->with([
                'success' => 'Successfully created campaign',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function view($campaign_id)
    {
        $campaign = Campaign::find($campaign_id);

        if (! $campaign) {
            return Redirect::back()->with([
                'errorMessage' => "This campaign ID '$campaign_id' does not exist!",
            ]);
        }

        $this->layout->page = view('home.campaign.view', [

            'campaign' => $campaign,
            'campaign_id' => $campaign_id,

        ]);
    }

    public function edit($campaign_id)
    {
        $campaign = Campaign::find($campaign_id);

        if (! $campaign) {
            return Redirect::back()->with([
                'errorMessage' => "This campaign ID '$campaign_id' does not exist!",
            ]);
        }

        $this->layout->page = view('home.campaign.edit', [

            'campaign' => $campaign,
            'campaign_id' => $campaign_id,

        ]);
    }

    public function edit_submit()
    {
        try {
            $campaign_id = Input::get('campaign_id');

            $title = Input::get('title');
            $teaser = Input::get('teaser');
            $show_at = Input::get('show_at');
            $stop_show_at = Input::get('stop_show_at');
            $body = Input::get('body');
            $img = Input::get('img');

            $campaign = Campaign::find($campaign_id);
            if (! $campaign) {
                return Redirect::back()->with([
                    'errorMessage' => "This campaign ID '$campaign_id' does not exist!",
                ]);
            }

            if ($img == 'default') {
                $img = 'null';
            }
            $campaign->title = $title;
            $campaign->teaser = $teaser;
            $campaign->show_from = $show_at;
            $campaign->show_to = $stop_show_at;
            $campaign->icon_img = $img;
            $campaign->body = $body;
            $campaign->save();

            return Redirect::back()->with([
                'successMessage' => 'Successfully saved changes',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
}
