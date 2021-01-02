<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use App\Models\ChangelogComment;
use App\Models\Email;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;


class ChangelogController extends Controller
{
    protected $layout = 'layouts.admin_website';
    private $names = [
        'test',
    ];

    public function index()
    {
        $pendingChangesets = Changelog::where('progress', '!=', 100)->get();
        $completedChangesets = Changelog::where('progress', 100)->get();

        $this->layout->page = view('home/changelog/index')->with([
            'pending' => $pendingChangesets,
            'completed' => $completedChangesets,
        ]);
    }

    public function viewChange($id)
    {
        $cs = Changelog::find($id);

        if (! $cs) {
            Redirect::back()->with(['errorMessage' => 'Changeset with id #'.$id.' does not exist!']);
        }

        $cs_comments = ChangelogComment::where('changelog_id', $id)->orderBy('id', 'DESC')->get();

        $this->layout->page = view('home/changelog/view')->with([
            'cs' => $cs,
            'cs_comments' => $cs_comments,
        ]);
    }

    public function viewChangeAction($id)
    {
        try {
            $cs = Changelog::find($id);

            if (! $cs) {
                throw new Exception("This changelog id '$id', does not exist!");
            }

            $action = Input::get('action');

            switch ($action) {

                case 'comment':

                    $comment = Input::get('comment');

                    if (trim($comment) == '') {
                        return Redirect::back()->with(['errorMessage' => 'Comment cannot be blank!']);
                    }

                    $newCsComment = new ChangelogComment();
                    $newCsComment->comment = $comment;
                    $newCsComment->changelog_id = $cs->id;
                    $newCsComment->user_id = Auth::user()->id;
                    $newCsComment->save();

                    if ($cs->track_progress) {
                        $this->notification($cs, 'comment');
                    }

                    return Redirect::back()->with(['successMessage' => 'Successfully commented on changeset #'.$cs->id]);

                break;

                default:
                    throw new Exception('No action defined in input!');
                break;

            }
        } catch (Exception $e) {
            return Redirect::back()->with(['errorMessage' => $e->getMessage()]);
        }
    }

    public function addChange()
    {
        try {
            $newChange = new Changelog();
            $newChange->title = Input::get('title');
            $newChange->details = Input::get('details');
            $newChange->progress = 0;
            $newChange->track_progress = (Input::get('track_progress') == 'on') ? 1 : 0;
            $newChange->email = Input::get('email');
            $newChange->created_at = date('Y-m-d H:i:s');
            $newChange->updated_at = date('Y-m-d H:i:s');
            $newChange->save();

            if (Auth::user()->email_addrss != $newChange->email) {
                Auth::user()->email_address = $newChange->email;
                Auth::user()->save();
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully created new change set #'.$newChange->id,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function removeChange()
    {
        try {
            $id = Input::get('id');
            $changeset = Changelog::find($id);

            if (! $changeset) {
                throw new Exception('This changeset id '.$id.', does not exist!');
            }

            $changeset->remove();

            return [
                'error' => 0,
                'success' => 1,
            ];
        } catch (Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function editChange()
    {
        try {
            $id = Input::get('id');
            $title = Input::get('title');
            $details = Input::get('details');
            $track_progress = (Input::get('track_progress') == 'on') ? true : false;
            $email = Input::get('email');

            //$progress = Input::get('progress');

            $changeset = Changelog::find($id);

            if (! $changeset) {
                throw new Exception('This changeset id '.$id.', does not exist!');
            }

            $changeset->title = $title;
            $changeset->details = $details;
            $changeset->track_progress = $track_progress;
            $changeset->email = $email;
            //$changeset->progress = $progress;
            $changeset->save();

            return Redirect::back()->with(['successMessage' => 'Successfully edited changeset #'.$changeset->id]);
        } catch (Exception $e) {
            return Redirect::back()->with(['errorMessage' => $e->getMessage()]);
        }
    }

    public function incrementChange()
    {
        try {
            $id = Input::get('id');
            $amount = intval(Input::get('amount'));

            $changeset = Changelog::find($id);

            if (! $changeset) {
                throw new Exception('This changeset id '.$id.', does not exist!');
            }

            $changeset->progress += $amount;

            if ($changeset->progress > 100) {
                $changeset->progress = 100;
            }

            if ($changeset->progress < 0) {
                $changeset->progress = 0;
            }

            $changeset->save();
            $term = ($amount >= 0) ? 'incremented' : 'decremented';

            if ($changeset->progress == 50) {
                if ($changeset->track_progress) {
                    $this->notification($changeset, 'halfway');
                }
            }

            return [
                'error' => 0,
                'success' => "Successfully $term changeset id $id by $amount.",
            ];
        } catch (Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function markCompleted()
    {
        try {
            $id = Input::get('id');

            $changeset = Changelog::find($id);

            if ($changeset->track_progress && $changeset->progress != 100) {
                $this->notification($changeset, 'completed');
            }

            if (! $changeset) {
                throw new Exception('This changeset id '.$id.', does not exist!');
            }

            $changeset->progress = 100;
            $changeset->save();

            return [
                'error' => 0,
                'success' => 1,
            ];
        } catch (Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function markUnCompleted()
    {
        try {
            $id = Input::get('id');

            $changeset = Changelog::find($id);

            if (! $changeset) {
                throw new Exception('This changeset id '.$id.', does not exist!');
            }

            $changeset->progress = 80;
            $changeset->save();

            return [
                'error' => 0,
                'success' => 1,
            ];
        } catch (Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendReminder()
    {
        try {
            $id = Input::get('id');

            $changeset = Changelog::find($id);

            $email = new Email();
            $email->to = SystemSetting::get('email_changeset_reminder');
            $email->title = "Changeset #$id reminder for completion";
            $email->body = "An Administrator has just sent you a reminder to complete changeset #$id:";
            $email->body .= '<br/><br/>';
            $email->body .= '<b>'.$changeset->title.'</b><br/>';
            $email->body .= $changeset->details;
            $email->body .= '<br/><br/><hr>';
            $email->body .= "<a href='".URL::to('changelog/view', ['id' => $id])."'>View this changeset &gt;&gt;</a>";
            $email->send();

            return [
                'error' => 0,
                'success' => 1,
            ];
        } catch (Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function notification($changeset, $type)
    {
        try {
            $sendEmail = false;

            $email = new Email();
            $email->sender = SystemSetting::get('email_changeset_name');
            $email->from = SystemSetting::get('email_changeset_from');
            $email->to = $changeset->email;
            $email->title = 'Changeset #'.$changeset->id.' update';

            $id = $changeset->id;

            switch ($type) {

                case 'completed':

                    $email->body = "<font color='#3fb103'>A changeset you have been tracking has just been marked as completed. ✔ </font>";
                    $email->body .= '<br/><br/>';
                    $email->body .= '<b>'.$changeset->title.'</b><br/>';
                    $email->body .= $changeset->details;
                    $email->body .= '<br/><br/><hr>';

                    $sendEmail = true;

                break;

                case 'halfway':

                    $email->body = "<font color='#f89406'>A changeset you have been tracking is 50% complete. ⌛ </font>";
                    $email->body .= '<br/><br/>';
                    $email->body .= '<b>'.$changeset->title.'</b><br/>';
                    $email->body .= $changeset->details;
                    $email->body .= '<br/><br/><hr>';

                    $sendEmail = true;

                break;

                case 'comment':

                    $lastComment = ChangelogComment::where('changelog_id', $changeset->id)->orderBy('id', 'DESC')->first();
                    $user = User::find($lastComment->user_id);

                    if ($lastComment && $user) {
                        $email->body = 'A changeset you have been tracking has just received a new comment. 💬';
                        $email->body .= '<br/><br/>';
                        $email->body .= '<b>'.$user->username.'</b> commented @ '.$lastComment->created_at.':<br/>';
                        $email->body .= $lastComment->comment;
                        $email->body .= '<br/><br/><hr>';
                        $sendEmail = true;
                    }

                break;

                default:
                    $sendEmail = false;
                break;
            }

            $email->body .= "<a href='".URL::to('changelog/view', ['id' => $changeset->id])."'>View this changeset &gt;&gt;</a>";

            if ($sendEmail) {
                $email->send();
            }
        } catch (Exception $e) {
        }
    }
}
