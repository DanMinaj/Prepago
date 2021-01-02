<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteControlTimes extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'remote_control_times';
    protected $primaryKey = 'id';
    protected $fillable = ['permanent_meter_id',
                            'day',
                            'active',
                            't1_start', 't1_end', 't1_active',
                            't2_start', 't2_end', 't2_active',
                            't3_start', 't3_end', 't3_active',
                            't4_start', 't4_end', 't4_active',
                            't5_start', 't5_end', 't5_active',
                            't6_start', 't6_end', 't6_active',
                            't7_start', 't7_end', 't7_active',
                            't8_start', 't8_end', 't8_active',
                            't9_start', 't9_end', 't9_active',
                            't10_start', 't10_end', 't10_active',
                          ];
    protected $guarded = ['id'];

    public $timestamps = false;
}
