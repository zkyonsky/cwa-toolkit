<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Assessee;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $query = Assessment::with('assessee.gov');
        $hasAssessee = true;

        if ($user->hasRole('User')) {
            $assessee = $user->assessee;
            if ($assessee) {
                // Filter by gov_id of the assessee
                $gov_id = $assessee->gov_id;
                $query->whereHas('assessee', function ($q) use ($gov_id) {
                    $q->where('gov_id', $gov_id);
                });
            } else {
                // If user has 'user' role but no assessee, return empty results
                $query->whereRaw('1 = 0');
                $hasAssessee = false;
            }
        }

        return Inertia::render("assessments/Index", [
            "assessments" => $query->get(),
            "hasAssessee" => $hasAssessee
        ]);
    }

    public function create()
    {
        return Inertia::render("assessments/Create", [
            "assessee_id" => Assessee::where("user_id", Auth::id())->first()->id
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "assessee_id" => "required|exists:assessees,id",
            "date" => "required|date",
        ]);

        Assessment::create($data);

        return redirect()->route("assessments.index")->with("message", "Assessment created successfully!");
    }

    public function edit(Assessment $assessment)
    {
        return Inertia::render("assessments/Edit", [
            "assessment" => $assessment,
            "assessee" => Assessee::with(['user', 'gov'])->where("id", $assessment->assessee_id)->first()
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $data = $request->validate([
            "assessee_id" => "required|exists:assessees,id",
            "date" => "required|date",
            "info" => "nullable|string",
            "result" => "nullable|string",
            "position" => "nullable|string",
            "contact" => "nullable|string",
        ]);

        $assessment->update([
            "date" => $data["date"],
            "info" => $data["info"],
        ]);

        // Update Assessee record
        $assessment->assessee->update([
            'position' => $data['position'],
            'contact' => $data['contact'],
        ]);

        return redirect()->route("assessments.index")->with("message", "Assessment updated successfully!");
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();
        return redirect()->route("assessments.index")->with("message", "Assessment deleted successfully!");
    }
}
