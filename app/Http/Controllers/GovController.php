<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Gov;

class GovController extends Controller
{
    public function index(Request $request)
    {
        $govs = Gov::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })->paginate(10)->withQueryString();

        return Inertia::render("govs/Index", [
            "govs" => $govs,
            "filters" => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render("govs/Create");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "code" => "required",
            "name" => "required",
            "level" => "required",
        ]);

        Gov::create($data);

        return redirect()->route("govs.index")->with("message", "Gov created successfully!");
    }

    public function edit(Gov $gov)
    {
        return Inertia::render("govs/Edit", [
            "gov" => $gov
        ]);
    }

    public function update(Request $request, Gov $gov)
    {
        $data = $request->validate([
            "code" => "required",
            "name" => "required",
            "level" => "required",
        ]);

        $gov->update($data);

        return redirect()->route("govs.index")->with("message", "Gov updated successfully!");
    }

    public function destroy(Gov $gov)
    {
        $gov->delete();
        return redirect()->route("govs.index")->with("message", "Gov deleted successfully!");
    }
}
