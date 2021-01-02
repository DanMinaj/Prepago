<?php

class Group extends Eloquent {

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
        if ($permissions)
        {
            return array_keys($permissions);
        }
        return [];
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }

}