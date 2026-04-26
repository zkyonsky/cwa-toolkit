<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;    
use Inertia\Inertia;    

class PermissionController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['permission:permissions.index'])->only('index');
    }

    /**
     * function index
     *
     * @return void
     */
    public function index()
    {
        $permissions = Permission::latest()->paginate(5);

        return Inertia::render('Permission', 
        ['permissions' => $permissions]);
    }
}
