<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institute;
use Illuminate\Support\Facades\Hash;
use App\Imports\InstituteImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InstituteTemplateExport;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Models\User;
class InstituteController extends Controller
{
    public function index(Request $request)
{
    $query = Institute::query();

    if ($search = $request->input('search')) {
        $query->where('name', 'like', "%$search%")
              ->orWhere('code', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('contact_phone', 'like', "%$search%");
    }

    $institutes = $query->orderBy('name')->paginate(10)->withQueryString();

    return view('admin.institutes.index', compact('institutes'));
}


    public function create()
    {
        return view('admin.institutes.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'code' => 'required|string|unique:institutes',
        'email' => 'nullable|email|unique:users,email', // prevent email conflict in users table
        'contact_phone' => 'nullable|string',
    ]);

    $defaultPassword = '1234inst';

    // Create the institute
    $institute = Institute::create([
        'name' => $request->name,
        'code' => $request->code,
        'email' => $request->email,
        'contact_phone' => $request->contact_phone,
        'password' => Hash::make($defaultPassword),
    ]);

    // Create a user login for this institute
    User::create([
        'name' => $institute->name,
        'email' => $institute->email ?? strtolower($institute->code) . '@nchmct.institute', // fallback email
        'role' => 'institute',
        'password' => Hash::make($defaultPassword),
    ]);

    return redirect()->route('admin.institutes.index')
        ->with('success', 'Institute and login user created successfully.');
}

    public function edit(Institute $institute)
    {
        return view('admin.institutes.edit', compact('institute'));
    }

    public function update(Request $request, Institute $institute)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:institutes,code,' . $institute->id,
            'email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'password' => 'nullable|min:6'
        ]);

        $institute->name = $request->name;
        $institute->code = $request->code;
        $institute->email = $request->email;
        $institute->contact_phone = $request->contact_phone;

        if ($request->filled('password')) {
            $institute->password = Hash::make($request->password);
        }

        $institute->save();

        return redirect()->route('admin.institutes.index')->with('success', 'Institute updated successfully.');
    }

    public function destroy(Institute $institute)
    {
        $institute->delete();
        return redirect()->route('admin.institutes.index')->with('success', 'Institute deleted.');
    }


public function bulkUpload(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    $import = new InstituteImport;

    try {
        Excel::import($import, $request->file('file'));

        if ($import->failures()->isNotEmpty()) {
            return back()->with([
                'warning' => 'Some rows failed validation.',
                'failures' => $import->failures()
            ]);
        }

        return back()->with('success', 'Institutes imported successfully.');
    } catch (ValidationException $e) {
        return back()->with('error', 'Validation failed: ' . $e->getMessage());
    } catch (\Exception $e) {
        return back()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}



public function downloadTemplate()
{
    return Excel::download(new InstituteTemplateExport, 'institute_template.xlsx');
}
}

