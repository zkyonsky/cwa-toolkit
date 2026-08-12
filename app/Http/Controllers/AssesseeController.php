<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessee;
use App\Models\Gov;
use App\Models\User;

class AssesseeController extends Controller
{
    public function index()
    {
        return Inertia::render("assessees/Index", [
            "assessees" => Assessee::with(['user', 'gov'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render("assessees/Create", [
            "govs" => Gov::select('id', 'name', 'level')->get(),
            "users" => User::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "gov_id" => "required|exists:govs,id",
            "user_id" => "required|exists:users,id",
            "position" => "required|string",
            "contact" => "required|string",
            "address" => "required|string",
        ]);

        Assessee::create($data);

        return redirect()->route("assessees.index")->with("message", "Assessee created successfully!");
    }

    public function edit(Assessee $assessee)
    {
        return Inertia::render("assessees/Edit", [
            "assessee" => $assessee,
            "govs" => Gov::select('id', 'name', 'level')->get(),
            "users" => User::select('id', 'name')->get()
        ]);
    }

    public function update(Request $request, Assessee $assessee)
    {
        $data = $request->validate([
            "gov_id" => "required|exists:govs,id",
            "user_id" => "required|exists:users,id",
            "position" => "required|string",
            "contact" => "required|string",
            "address" => "required|string",
        ]);

        $assessee->update($data);

        return redirect()->route("assessees.index")->with("message", "Assessee updated successfully!");
    }

    public function destroy(Assessee $assessee)
    {
        $assessee->delete();
        return redirect()->route("assessees.index")->with("message", "Assessee deleted successfully!");
    }
}
