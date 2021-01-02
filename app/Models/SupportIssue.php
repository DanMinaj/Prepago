<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;


class SupportIssue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'support_issues';

    public $timestamps = true;

    public function getIssueTitleAttribute()
    {
        if (empty($this->attributes['issue_title'])) {
            return '- Empty -';
        }

        return $this->attributes['issue_title'];
    }

    public function getStatusAttribute()
    {
        if ($this->resolved) {
            return 'Resolved';
        }

        if ($this->viewed && $this->started) {
            return 'Started';
        }

        if (! $this->viewed) {
            return 'Un-viewed';
        }
    }

    public function getPageAttribute()
    {
        if (strpos($this->attributes['page'], 'customer_tabview') !== false) {
            return '';
        } else {
            return $this->attributes['page'];
        }
    }

    public function getCustomerAttribute()
    {
        $customer = Customer::find($this->customer_ID);

        if ($customer) {
            return $customer->username;
        }

        return 'N/A';
    }

    public function getCustomerLinkAttribute()
    {
        $customer = Customer::find($this->customer_ID);

        if ($customer) {
            return URL::to('customer_tabview_controller/show', ['customer_id' => $customer->id]);
        }

        return 'N/A';
    }

    public function getSchemeAttribute()
    {
        $scheme = Scheme::find($this->scheme_ID);

        if ($scheme) {
            return $scheme->company_name;
        }

        return 'N/A';
    }

    public function statusCss($background = false)
    {
        if ($this->status == 'Un-viewed') {
            if ($background) {
                return "style='background: #006dcc;'";
            } else {
                return "style='color: #006dcc;'";
            }
        }

        if ($this->status == 'Started') {
            if ($background) {
                return "style='background: #faa732;'";
            } else {
                return "style='color: #faa732;'";
            }
        }

        if ($this->status == 'Resolved') {
            if ($background) {
                return "style='background: #5bb75b;'";
            } else {
                return "style='color: #5bb75b;'";
            }
        }
    }

    public function reply($reply, $operator_id)
    {
        $reply_obj = null;

        try {
            $date = date('Y-m-d H:i:s');
            DB::table('support_issues_replies')->insert([

                'issue_ID' => $this->id,
                'operator_ID' => $operator_id,
                'reply' => $reply,
                'created_at' => $date,
                'updated_at' => $date,

            ]);

            $reply_obj = DB::table('support_issues_replies')->where('issue_ID', $this->id)->where('operator_ID', $operator_id)->where('created_at', $date)->where('reply', $reply)->first();
        } catch (Exception $e) {
            return 'Error: '.$e->getMessage();
        }

        return $reply_obj;
    }

    public function getRepliesAttribute()
    {
        $replies = DB::table('support_issues_replies')->where('issue_ID', $this->id)->orderBy('id')->get();

        foreach ($replies as $r) {
            $r->operator = User::where('id', $r->operator_ID)->first()->employee_name;
        }

        return $replies;
    }
}
