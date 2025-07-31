<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class AcademicSessionController extends Controller
{
  public function index()
{
    
    $latestRegularSession = AcademicSession::where('type', 'regular')->latest()->first();
    $latestDiplomaSession = AcademicSession::where('type', 'diploma')->latest()->first();

    return view('admin.academic_sessions.index', [
        // 'academicSession'   => $academicSession, // 👈 now available to the Blade
        'regularSessionId'  => $latestRegularSession?->id,
        'diplomaSessionId'  => $latestDiplomaSession?->id,
    ]);
}



    public function create(Request $request)
    {
        $academic_session = null;
        $type = $request->query('type');

        return view('admin.academic_sessions.create', compact('academic_session', 'type'));
    }

   public function store(Request $request): RedirectResponse
{
   $validated = $request->validate([
    'year' => 'required|string',
    'term' => 'required|in:Jan,July',
    'type' => 'required|in:regular,diploma',
    'odd_even' => 'required_if:type,regular|nullable|in:odd,even',
    'diploma_year' => 'required_if:type,diploma|nullable|in:1,2',
    'active' => 'boolean',
]);

    // Check for duplicate session
    $exists = AcademicSession::where('year', $validated['year'])
        ->where('term', $validated['term'])
        ->where('type', $validated['type'])
        ->where(function ($query) use ($validated) {
            if ($validated['type'] === 'regular') {
                $query->where('odd_even', $validated['odd_even'])
                      ->whereNull('diploma_year');
            } else {
                $query->where('diploma_year', $validated['diploma_year'])
                      ->whereNull('odd_even');
            }
        })
        ->exists();

    if ($exists) {
        return back()
            ->withErrors(['year' => 'The academic session already exists.'])
            ->withInput();
    }

   AcademicSession::create([
    'year' => $request->year,
    'term' => $request->term,
    'type' => $request->type,
    'odd_even' => $request->type === 'regular' ? $request->odd_even : null,
    'diploma_year' => $request->type === 'diploma' ? $request->diploma_year : null,
    'active' => $request->has('active'),
]);
    $route = $validated['type'] === 'regular'
        ? 'admin.academic_sessions.regular.index'
        : 'admin.academic_sessions.diploma.index';

    return redirect()
        ->route($route)
        ->with('success', 'Academic session created successfully.');
}


  public function edit(AcademicSession $academic_session)
{
    $type = $academic_session->type;
    return view('admin.academic_sessions.create', compact('academic_session', 'type'));
}


public function update(Request $request, AcademicSession $academic_session): RedirectResponse
{
    $validated = $request->validate([
        'year' => 'required|string',
        'term' => 'required|in:Jan,July',
        'type' => 'required|in:regular,diploma',
        'odd_even' => 'required_if:type,regular|nullable|in:odd,even',
        'diploma_year' => 'required_if:type,diploma|nullable|in:1,2',
        'active' => 'boolean',
    ]);

    // Check for duplicate (excluding current session)
    $exists = AcademicSession::where('year', $validated['year'])
        ->where('term', $validated['term'])
        ->where('type', $validated['type'])
        ->where(function ($query) use ($validated) {
            if ($validated['type'] === 'regular') {
                $query->where('odd_even', $validated['odd_even'])
                      ->whereNull('diploma_year');
            } else {
                $query->where('diploma_year', $validated['diploma_year'])
                      ->whereNull('odd_even');
            }
        })
        ->where('id', '!=', $academic_session->id)
        ->exists();

    if ($exists) {
        return back()
            ->withErrors(['year' => 'The academic session already exists.'])
            ->withInput();
    }

    // ✅ Correct: Update the existing session
    $academic_session->update([
        'year' => $validated['year'],
        'term' => $validated['term'],
        'type' => $validated['type'],
        'odd_even' => $validated['type'] === 'regular' ? $validated['odd_even'] : null,
        'diploma_year' => $validated['type'] === 'diploma' ? $validated['diploma_year'] : null,
       'active' => $request->input('active') == 1 ? true : false,

    ]);

    $route = $validated['type'] === 'regular'
        ? 'admin.academic_sessions.regular.index'
        : 'admin.academic_sessions.diploma.index';

    return redirect()
        ->route($route)
        ->with('success', 'Academic session updated successfully.');
}



    public function destroy(AcademicSession $academic_session)
    {
        if ($academic_session->active) {
            return back()->with('error', 'Cannot delete the active session.');
        }

        $academic_session->delete();

        return redirect()->route('admin.academic_sessions.index')
            ->with('success', 'Session deleted successfully.');
    }

    public function mapPrograms(AcademicSession $session, Request $request)
    {
        $type = $request->query('type');

        $programs = Program::when($type === 'regular', fn ($q) => $q->where('structure', 'semester'))
            ->when($type === 'diploma', fn ($q) => $q->where('structure', 'yearly'))
            ->get();

        return view('admin.academic_sessions.map-programs', compact('session', 'programs'));
    }

    public function storeProgramMappings(Request $request, AcademicSession $session)
    {
        $selectedIndexes = $request->input('selected', []);
        $mappings = $request->input('mappings', []);

        foreach ($selectedIndexes as $index) {
            if (!isset($mappings[$index])) continue;

            $map = $mappings[$index];

            $validated = validator($map, [
                'program_id' => 'required|exists:programs,id',
                'structure'  => 'required|in:semester,yearly',
                'semester'   => 'nullable|string|max:50',
            ])->validate();

            DB::table('academic_session_program')->updateOrInsert(
                [
                    'academic_session_id' => $session->id,
                    'program_id'          => $validated['program_id'],
                    'semester'            => $validated['semester'] ?? null,
                ],
                [
                    'structure'   => $validated['structure'],
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }

        return redirect()->route('admin.academic_sessions.index')
            ->with('success', 'Program mapped to session successfully.');
    }

public function listRegular()
{
    $sessions = AcademicSession::where('type', 'regular')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.academic_sessions.regular.index', compact('sessions'));
}


    public function listDiploma()
    {
       

        return view('admin.academic_sessions.diploma.index', compact('sessions'));
    }

//     public function showRegular(AcademicSession $academic_session)
// {
//     $programs = $academic_session
//         ->programs()
//         ->withPivot('structure', 'semester') // removed 'start_level'
//         ->get();



//     return view('admin.academic_sessions.regular.show', compact('academic_session', 'programs'));
// }

public function showRegular(AcademicSession $academic_session)
{
    $programs = $academic_session
    ->programs()
    ->with([
        'students' => function ($q) use ($academic_session) {
            $q->where('academic_session_id', $academic_session->id)->orderBy('name');
        }
    ])
    ->withPivot('structure', 'semester')
    ->get();
    return view('admin.academic_sessions.regular.show', compact('academic_session', 'programs'));
}



    public function showDiploma(AcademicSession $academic_session)
    {
        $programs = $academic_session
            ->programs()
            ->wherePivot('structure', 'yearly')
            ->with('courses')
            ->get();

        return view('admin.academic_sessions.diploma.show', compact('academic_session', 'programs'));
    }

    public function splitView()
    {
        $regular = AcademicSession::whereHas('programs', fn ($q) =>
            $q->where('academic_session_program.structure', 'semester')
        )->with('programs')->get();

        $diploma = AcademicSession::whereHas('programs', fn ($q) =>
            $q->where('academic_session_program.structure', 'yearly')
        )->with('programs')->get();

        return view('admin.academic_sessions.split', compact('regular', 'diploma'));
    }
}
