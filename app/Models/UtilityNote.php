<?php

class UtilityNote extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'utility_notes';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
