<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Group extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'permissions'];

    public function getPermissionsAttribute($value)
    {
        $permissions = json_decode($value, true);
        if ($permissions) {
            return array_keys($permissions);
        }

        return [];
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }
}
