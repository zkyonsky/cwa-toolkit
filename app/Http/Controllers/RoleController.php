<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("roles/Index", [
            "roles" => Role::with('permissions')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("roles/Create", [
            "permissions" => Permission::pluck("name")->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "permissions" => "required",
        ]);

        $role = Role::create($request->all());
        $role->syncPermissions($request->permissions);

        return redirect()->route("roles.index")->with("message", "Role created successfully!");
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {

        return Inertia::render("roles/Edit", [
            "role" => $role,
            "selected_permissions" => Permission::whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id);
            })->pluck("name")->all(),
            "permissions" => Permission::pluck("name")->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            "name" => "required",
            "permissions" => "array",
        ]);

        $role->update([
            "name" => $request->name
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route("roles.index")->with("message", "Role updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route("roles.index")->with("message", "Role deleted successfully!");
    }
}
