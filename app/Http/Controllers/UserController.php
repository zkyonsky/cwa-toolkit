<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Gov;
use App\Models\Assessee;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("users/Index", [
            "users" => User::with('roles')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("users/Create", [
            "roles" => Role::pluck("name")->all(),
            "govs" => Gov::select('id', 'name', 'level')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required",
            "email" => "required",
            "password" => "required",
            "role" => "required",
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => bcrypt($data["password"])
        ]);
        
        if ($request->assessee) { 

            $request->validate([
                "gov" => "required",
            ]);

            Assessee::create([
                "user_id" => $user->id,
                "gov_id" => $request->gov,
            ]);
        }
        
        
        $user->assignRole($data["role"]);

        return redirect()->route("users.index")->with("message", "User created successfully!");
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
    public function edit(User $user)
    {
        $userRole = $user->roles->pluck('name')->first();
        return Inertia::render("users/Edit", [
            "user" => $user,
            "roles" => Role::pluck("name")->all(),
            "userRole" => $userRole
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            "name" => "required",
            "email" => "required",
            "password" => "nullable",
            "role" => "required",
        ]);

        $updateData = [
            "name" => $data["name"],
            "email" => $data["email"]
        ];

        if ($data["password"]) {
            $updateData["password"] = bcrypt($data["password"]);
        }
        
        $user->update($updateData);
        $user->syncRoles($data["role"]);

        return redirect()->route("users.index")->with("message", "User updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route("users.index")->with("message", "User deleted successfully!");
    }

    /**
     * Import users from CSV file.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->route("users.index")->with("message", "CSV file is empty or invalid.");
        }

        // Normalize header names (trim whitespace)
        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $index => $key) {
                if (trim($key) !== '') {
                    $data[trim($key)] = isset($row[$index]) ? $row[$index] : null;
                }
            }
            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
                $skipped++;
                $errors[] = "Row {$rowNumber}: Missing required fields (name, email, password, or role).";
                continue;
            }

            // Check if email already exists
            if (User::where('email', trim($data['email']))->exists()) {
                $skipped++;
                $errors[] = "Row {$rowNumber}: Email '{$data['email']}' already exists.";
                continue;
            }

            // Check if role exists
            $roleName = trim($data['role']);
            if (!Role::where('name', $roleName)->exists()) {
                $skipped++;
                $errors[] = "Row {$rowNumber}: Role '{$roleName}' does not exist.";
                continue;
            }

            // Create user
            $user = User::create([
                'name' => trim($data['name']),
                'email' => trim($data['email']),
                'password' => bcrypt(trim($data['password'])),
            ]);

            $user->assignRole($roleName);

            // Create assessee if applicable
            $isAssessee = isset($data['assessee']) && strtolower(trim($data['assessee'])) === 'yes';
            if ($isAssessee) {
                if (!empty($data['gov_id'])) {
                    $govId = trim($data['gov_id']);
                    if (Gov::where('id', $govId)->exists()) {
                        Assessee::create([
                            'user_id' => $user->id,
                            'gov_id' => $govId,
                        ]);
                    } else {
                        $errors[] = "Row {$rowNumber}: User created but Gov ID '{$govId}' does not exist, assessee not assigned.";
                    }
                } else {
                    $errors[] = "Row {$rowNumber}: User created but Gov ID is missing, assessee not assigned.";
                }
            }

            $imported++;
        }

        fclose($handle);

        $message = "{$imported} user(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped.";
        }
        if (!empty($errors)) {
            $message .= " Details: " . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " ... and " . (count($errors) - 5) . " more.";
            }
        }

        return redirect()->route("users.index")->with("message", $message);
    }

    /**
     * Download CSV template for user import.
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/user_import_template.csv');
        return response()->download($filePath, 'user_import_template.csv', [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}
