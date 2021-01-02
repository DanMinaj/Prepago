<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportABugPrepayGO extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'report_a_bug_prepaygo';

    public function getPreviewAttribute()
    {
        return implode(' ', array_slice(str_word_count($this->description, 2), 0, 5)).' .. ';
    }

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }
}
