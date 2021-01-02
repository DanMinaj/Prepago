<?php

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as Response;

class AnnouncementsController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function view_announcements()
    {
        try {
            $announcements = Announcement::orderBy('id', 'DESC')->get();

            $this->layout->page = view('home.announcements_all', [
                'announcements' => $announcements,
            ]);
        } catch (Exception $e) {
        }
    }

    public function create_announcement()
    {
        $this->layout->page = view('home.announcements', [

        ]);
    }

    public function create_announcement_submit()
    {
        try {
            $title = Input::get('title');
            $teaser = Input::get('teaser');
            $body = Input::get('body');
            $image = Input::get('image');
            $show_at = Input::get('show_at');
            $stop_show_at = Input::get('stop_show_at');

            if ($image == 'default') {
                $image = null;
            }

            $announcement = new Announcement();
            $announcement->teaser = $teaser;
            $announcement->title = $title;
            $announcement->body = $body;
            $announcement->img = $image;
            $announcement->show_at = $show_at;
            $announcement->stop_show_at = $stop_show_at;
            $announcement->date = date('Y-m-d');

            //$announcement->scheme_id = date('Y-m-d');
            $announcement->save();

            return redirect('announcements/all')->with([
                'successMessage' => 'Successfully created new announcement',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function view_announcement($announcement_id)
    {
        $announcement = Announcement::find($announcement_id);

        if (! $announcement) {
            return Redirect::back()->with([
                'errorMessage' => "This announcement ID '$announcement_id' does not exist!",
            ]);
        }

        $this->layout->page = view('home.announcements_view', [

            'announcement' => $announcement,
            'announcement_id' => $announcement_id,

        ]);
    }

    public function edit_announcement($announcement_id)
    {
        $announcement = Announcement::find($announcement_id);

        if (! $announcement) {
            return Redirect::back()->with([
                'errorMessage' => "This announcement ID '$announcement_id' does not exist!",
            ]);
        }

        $this->layout->page = view('home.announcements_edit', [

            'announcement' => $announcement,
            'announcement_id' => $announcement_id,

        ]);
    }

    public function edit_announcement_submit()
    {
        try {
            $announcement_id = Input::get('announcement_id');

            $title = Input::get('title');
            $teaser = Input::get('teaser');
            $show_at = Input::get('show_at');
            $stop_show_at = Input::get('stop_show_at');
            $body = Input::get('body');
            $img = Input::get('img');

            $announcement = Announcement::find($announcement_id);
            if (! $announcement) {
                return Redirect::back()->with([
                    'errorMessage' => "This announcement ID '$announcement_id' does not exist!",
                ]);
            }

            if ($img == 'default') {
                $img = 'null';
            }
            $announcement->title = $title;
            $announcement->teaser = $teaser;
            $announcement->show_at = $show_at;
            $announcement->stop_show_at = $stop_show_at;
            $announcement->img = $img;
            $announcement->body = $body;
            $announcement->save();

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
