<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;



class GroupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $groups = Group::whereNotNull('permissions')->orderBy('name')->get();

        $this->layout->page = view('group/index')->with('groups', $groups);
    }

    public function update()
    {
        $passedPermissions = Input::get('permissions');
        $permissions = array_fill_keys($passedPermissions, '1');

        if (! Group::findOrFail(Input::get('group_id'))->update([
               'permissions' => $permissions,
            ])) {
            Session::flash('errorMessage', 'The permissions cannot be updated at the moment');
        } else {
            Session::flash('successMessage', 'The permissions were updated successfully');
        }

        return redirect('groups');
    }
}
