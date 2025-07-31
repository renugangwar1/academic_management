<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Admin\InstituteController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\ResultsController;
use App\Http\Controllers\Admin\ReappearController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController; // ✅ Aliased
use App\Http\Controllers\Admin\ExaminationController;
use App\Http\Controllers\Admin\ExamSwitchController;
use App\Http\Controllers\Admin\RegularMarkController;
use App\Http\Controllers\Admin\DiplomaMarkController;
use App\Http\Controllers\Admin\StudentPromotionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

use App\Http\Controllers\Institute\DashboardController as InstituteDashboardController;
use App\Http\Controllers\Institute\StudentController as InstituteStudentController; // ✅ Aliased
use App\Http\Controllers\Institute\MessageController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth', 'role:admin'])
      ->prefix('admin')
      ->as('admin.')
      ->group(function () {

    /* Dashboard */
    // Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
  Route::get('/student-uploads/{upload}/download', [AdminDashboardController::class, 'download'])->name('studentUploads.download');
    Route::post('/student-uploads/{upload}/approve', [AdminDashboardController::class, 'approve'])->name('studentUploads.approve');
    Route::post('/student-uploads/{upload}/reject', [AdminDashboardController::class, 'reject'])->name('studentUploads.reject');
    Route::post('/messages/read/{id}', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');

    /* Academic session views */
    Route::get('academic_sessions/regular',  [AcademicSessionController::class,'listRegular'])->name('academic_sessions.regular.index');
    Route::get('academic_sessions/diploma',  [AcademicSessionController::class,'listDiploma'])->name('academic_sessions.diploma.index');

    Route::get('academic_sessions/regular/{academic_session}', [AcademicSessionController::class,'showRegular'])
        ->whereNumber('academic_session')->name('academic_sessions.regular.show');

    Route::get('academic_sessions/diploma/{academic_session}', [AcademicSessionController::class,'showDiploma'])
        ->whereNumber('academic_session')->name('academic_sessions.diploma.show');

    /* Resource controllers */
  Route::resource('institutes', InstituteController::class)
         ->except(['show']);   
    Route::resource('programs',           ProgramController::class);
    Route::resource('courses',            CourseController::class)->except(['show']);
    Route::resource('academic_sessions',  AcademicSessionController::class);
    Route::resource('reappears',          ReappearController::class);
    Route::resource('students',            AdminStudentController::class)->except(['show']);
Route::post('/institutes/bulk-upload', [App\Http\Controllers\Admin\InstituteController::class, 'bulkUpload'])->name('institutes.bulk-upload');
Route::get('/institutes/template',
    [App\Http\Controllers\Admin\InstituteController::class, 'downloadTemplate']
)->name('institutes.template');
  Route::post('add-users', [InstituteController::class, 'bulkAddUsers'])->name('add-users');





    /* Program extras */
    Route::get('/programs/{program}/settings', [ProgramController::class,'settings'])->name('programs.settings');
    Route::get('/{id}/info',                    [ProgramController::class,'viewInfo'])  ->name('info');
    Route::get('/{id}/courses',                 [ProgramController::class,'viewCourses'])->name('courses');
    Route::get('/{id}/students',                [ProgramController::class,'viewStudents'])->name('students');
    Route::get('/programs/{program}/institutes',[ProgramController::class,'viewInstitutes'])->name('programs.institutes');
    Route::post('/programs/{program}/institutes',[ProgramController::class,'updateInstitutes'])->name('programs.institutes.update');

    /* Student import/export */
   Route::post('/students/import/{programId}', [AdminStudentController::class, 'import'])->name('students.import');
Route::get('/students/export', [AdminStudentController::class, 'export'])->name('students.export');

    Route::get ('/programs/{id}/import-students',         [ProgramController::class,'showImportForm'])->name('programs.import.form');
    Route::post('/programs/{id}/import-students',         [ProgramController::class,'importStudents'])->name('programs.import');
    Route::get ('/programs/{id}/students-template',       [ProgramController::class,'downloadStudentTemplate'])->name('programs.students.template');
    Route::get ('/programs/{id}/students/export',         [ProgramController::class,'exportStudents'])->name('programs.students.export');
    Route::get ('/students/export',                       [AdminStudentController::class,'export'])->name('students.export');
/*promote students*/
 Route::get('/promotions/selection', [StudentPromotionController::class, 'promote'])->name('promote.selection');
   
    Route::post('/promotions/single/{studentId}', [StudentPromotionController::class, 'promoteSingle'])->name('promote.single');
Route::post('/students/promote', [StudentPromotionController::class, 'promote'])->name('promote');
Route::get('/promotions/manual', [StudentPromotionController::class, 'promoteManual'])
    ->name('promotions.manual'); 
Route::post('/promotions/manual', [StudentPromotionController::class, 'promoteManual'])
    ->name('promote.manual.submit');
Route::get('/promotions/session-summary', [StudentPromotionController::class, 'listProgramSemesterSessions'])->name('promotions.sessions');

    /* Assigned courses */
    Route::get ('/programs/{id}/assign-courses',            [ProgramController::class,'showAssignCoursesForm'])->name('programs.assign.courses');
    Route::post('/programs/{id}/assigned-courses/save',     [ProgramController::class,'saveAssignments'])->name('programs.save.assigned.courses');
    Route::post('/programs/{id}/assigned-courses/import',   [ProgramController::class,'importAssignments'])->name('programs.import.assigned.courses');
    Route::get ('/programs/{id}/assigned-courses/template', [ProgramController::class,'downloadAssignmentTemplate'])->name('programs.template.assigned.courses');
Route::get('/programs/{id}/students', [ProgramController::class, 'viewStudents'])->name('programs.view_students');

    /* Course components */
       Route::post('/courses/import', [CourseController::class, 'import'])->name('courses.import');
         Route::get('/courses/template-download/{programId}', [CourseController::class, 'downloadTemplate'])
        ->name('courses.template.download');
    Route::get ('courses/components',         [CourseController::class,'showComponentForm'])->name('courses.components');
    Route::get ('courses/{id}/add-component', [CourseController::class,'addComponent'])->name('courses.component.add');
    Route::post('courses/{id}/save-component',[CourseController::class,'saveComponent'])->name('courses.component.save');
    Route::get ('courses/{id}/view-component',[CourseController::class,'viewComponent'])->name('courses.component.view');
    Route::post('/courses/component/copy/{sourceCourse}', [CourseController::class,'copyComponent'])->name('courses.component.copy');
Route::get('courses/bulk-map-form', [CourseController::class, 'BulkMapForm'])
    ->name('courses.bulk.map.form');
Route::get('courses/mapping', [CourseController::class, 'showMappingForm'])
    ->name('courses.mapping');
    // Add this inside your 'admin' route group if applicable
Route::post('/courses/mapping/store', [CourseController::class, 'storeMapping'])->name('courses.mapping.store');

Route::post('/courses/bulk-map', [CourseController::class, 'bulkMapStore'])
        ->name('courses.bulk.map.store');



Route::post('/reappears/download-one', [ReappearController::class, 'downloadReappearSingle'])->name('admitcard.reappear.single');
       Route::get('examination', [ExamSwitchController::class, 'dashboard'])->name('examination.index');
Route::match(['get', 'post'], 'switch/{type}', [ExamSwitchController::class, 'switch'])
    ->whereIn('type', ['regular', 'diploma'])
    ->name('programme.switch');



Route::get('/messages', [App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/chat/{institute_id}', [App\Http\Controllers\Admin\MessageController::class, 'chat'])->name('messages.chat');
Route::post('/messages/{id}/reply', [App\Http\Controllers\Admin\MessageController::class, 'reply'])->name('messages.reply');

Route::post('/exam/set-session', [ExaminationController::class, 'setExamSession'])->name('exam.setSession');

/* ────────────── REGULAR ROUTES ────────────── */
Route::prefix('regular')->as('regular.')->group(function () {
    // View Routes
    Route::get('exams/index', [ExaminationController::class, 'indexRegular'])->name('exams.index');
  Route::get('/exams/marks-upload/{session}', [ExaminationController::class, 'uploadMarksRegular'])
    ->name('exams.marks.upload');

    Route::get('exams/admit-card', [ExaminationController::class, 'generateAdmitCard'])
       ->name('exams.admitcard');
   Route::get('exams/results', [ExaminationController::class, 'showResultPageRegular'])
    ->name('exams.results');


    // POST Actions
    Route::post('exams/upload-marks', [RegularMarkController::class, 'handleUploadMarksRegular'])->name('exams.upload-marks.file');
    Route::post('exams/{session}/finalize-marks', [RegularMarkController::class, 'finalizeMarks'])
        ->whereNumber('session')->name('exams.finalize.marks');
    Route::post('exams/download-uploaded-marks', [RegularMarkController::class, 'downloadUploadedMarks'])->name('exams.download-uploaded-marks');
    Route::post('exams/compile-internal', [RegularMarkController::class, 'compileInternalMarks'])->name('exams.compile.internal');

    // Results
    Route::post('exams/results/compile/{session}', [ResultsController::class, 'compileFinalResultsRegular'])
        ->whereNumber('session')->name('exams.results.compile');
    Route::post('exams/results/external/{session}', [ResultsController::class, 'calculateExternalResultsRegular'])
        ->whereNumber('session')->name('exams.results.external');
    Route::post('exams/results/publish', [ResultsController::class, 'publishResultRegular'])->name('exams.results.publish');

    // Downloads
    Route::get('exams/results/download-excel', [ResultsController::class, 'downloadExcelRegular'])->name('exams.results.download');
Route::get('/admin/exams/external-results/download/{academic_session_id}/{program_id}/{semester}', 
    [ResultsController::class, 'downloadExternalResults']
)->name('admin.exams.external-results.download');

    // Templatesfinalize.marks
    Route::get('exams/download-template', [RegularMarkController::class, 'downloadTemplate'])->name('exams.template');

Route::get('/exams/view-result', [ExaminationController::class, 'viewResult'])->name('exams.results.view');



 

});


/* ────────────── DIPLOMA ROUTES ────────────── */
Route::prefix('diploma')->as('diploma.')->group(function () {
    // View Routes
    Route::get('exams/index', [ExaminationController::class, 'indexDiploma'])->name('exams.index');
    Route::get('exams/{session}/marks-upload', [ExaminationController::class, 'uploadMarksDiploma'])
        ->whereNumber('session')->name('exams.marks.upload');
    Route::get('exams/{session}/admit-card', [ExaminationController::class, 'generateAdmitCard'])
        ->whereNumber('session')->name('exams.admitcard');
    Route::get('exams/results/{session}', [ExaminationController::class, 'showResultPageDiploma'])
        ->whereNumber('session')->name('exams.results');

    // POST Actions
    Route::post('exams/upload-marks', [ExaminationController::class, 'handleUploadMarksDiploma'])->name('exams.upload-marks.file');
    Route::post('exams/finalize-marks', [DiplomaMarkController::class, 'finalizeMarks'])->name('exams.finalize.marks');
    Route::post('exams/download-uploaded-marks', [DiplomaMarkController::class, 'downloadUploadedMarks'])->name('exams.download-uploaded-marks');
    Route::post('exams/compile-internal', [DiplomaMarkController::class, 'compileInternalMarks'])->name('exams.compile.internal');

    // Results
    Route::post('exams/results/compile/{session}', [ResultsController::class, 'compileFinalResultsDiploma'])
        ->whereNumber('session')->name('exams.results.compile');
    Route::post('exams/results/external/{session}', [ResultsController::class, 'calculateExternalResultsDiploma'])
        ->whereNumber('session')->name('exams.results.external');
    Route::post('exams/results/publish', [ResultsController::class, 'publishResultDiploma'])->name('exams.results.publish');

    // Downloads
    Route::get('exams/results/download-excel', [ResultsController::class, 'downloadExcelDiploma'])->name('exams.results.download');
    Route::get('exams/results/{program_id}/{semester}/external-download-excel', [ResultsController::class, 'downloadExternalResultsDiploma'])->name('exams.external-results.download');

    // Templates
    Route::get('exams/download-template', [DiplomaMarkController::class, 'downloadTemplate'])->name('exams.template');
});
   Route::get('/exams/results',        [ExaminationController::class, 'indexRegular'])
         ->name('exams.results.index');     // list view
    Route::get('/exams/results/show',   [ExaminationController::class, 'showRegular'])
         ->name('exams.results.show');      // detail view

/* ────────────── SHARED / COMMON ROUTES ────────────── */
Route::post('exams/fetch-courses', [ExaminationController::class, 'fetchCourses'])->name('exams.fetch.courses');
Route::get ('exams/calculated-results', [ResultsController::class, 'showCalculatedResults'])->name('exams.calculated');

Route::get('/exams/external-results/download', [ResultsController::class, 'downloadExcel'])
     ->name('exams.external-results.download');

Route::post('/exams/results/publish', [ResultsController::class, 'publish'])
    ->name('exams.results.publish');
Route::get('/ajax/fetch-courses', [ExaminationController::class, 'fetchCourses']);



Route::post('/exams/results/download-bulk', [ResultsController::class, 'downloadBulkResults'])
    ->name('exams.results.download-bulk');

// For Individual Roll Number Result
Route::post('/exams/results/download-roll', [ResultsController::class, 'downloadResultByRoll'])
    ->name('exams.results.download-roll');
Route::post('/results/aggregate', [ResultsController::class, 'compileAggregatedResult'])->name('results.aggregate');
Route::post('/results/aggregate-all', [ResultsController::class, 'aggregateAll'])->name('results.aggregate-all');


    /* Admit card and Reappear downloads */
    Route::post('/admitcard/bulk',         [ExaminationController::class,'downloadBulkAdmitCards'])->name('admitcard.bulk');
    Route::post('/admitcard/single',       [ExaminationController::class,'downloadSingleAdmitCard'])->name('admitcard.single');
    Route::post('/reappears/download',     [ReappearController::class,'downloadReappear'])->name('admitcard.reappear');
    Route::post('/reappears/download-one', [ReappearController::class,'downloadReappearSingle'])->name('admitcard.reappear.single');
Route::get('/ajax/academic-sessions/{program}', [\App\Http\Controllers\Admin\ReappearController::class, 'getAcademicSessionsByProgram']);

    /* Academic session ↔ program mapping */
    Route::get ('academic_sessions/{session}/map-programs',   [AcademicSessionController::class,'mapPrograms'])->name('academic_sessions.mapPrograms');
    Route::post('academic_sessions/{session}/map-programs',   [AcademicSessionController::class,'storeProgramMappings'])->name('academic_sessions.mapPrograms.store');
});


//////////////////////////////////////
// -------------------------------------
// INSTITUTE Dashboard
// -------------------------------------
////////////////////////////////////////

Route::middleware(['auth', 'role:institute'])
    ->prefix('institute')
    ->as('institute.')
    ->group(function () {
        Route::get('dashboard', [InstituteDashboardController::class, 'index'])->name('dashboard');
        Route::get('/examinations', [InstituteDashboardController::class, 'examinations'])->name('examinations');
        Route::get('/reappears', [InstituteDashboardController::class, 'reappears'])->name('reappears');
        
        Route::get('/programs', [\App\Http\Controllers\Institute\ProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/{id}', [\App\Http\Controllers\Institute\ProgramController::class, 'show'])->name('programs.show');

        Route::get('/students', [InstituteStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [InstituteStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [InstituteStudentController::class, 'store'])->name('students.store');
        Route::post('/students/import', [InstituteStudentController::class, 'import'])->name('students.import');
      Route::get('/students/download-template', [InstituteStudentController::class, 'downloadTemplate'])
    ->name('students.downloadTemplate');

        Route::get('/students/export', [InstituteStudentController::class, 'export'])->name('students.export');

 Route::get('message', [MessageController::class, 'create'])->name('message.index'); // optional alias

        Route::post('message', [MessageController::class, 'store'])->name('message.store');
        Route::get('message/create', [MessageController::class, 'create'])->name('message.create');

    });

//  Route::middleware(['auth:institute'])
//     ->prefix('institute')
//     ->as('institute.')
//     ->group(function () {

//         Route::get('dashboard', [InstituteDashboardController::class, 'index'])->name('dashboard');
//         Route::get('/examinations', [InstituteDashboardController::class, 'examinations'])->name('examinations');
//         Route::get('/reappears', [InstituteDashboardController::class, 'reappears'])->name('reappears');

//         Route::get('/programs', [\App\Http\Controllers\Institute\ProgramController::class, 'index'])->name('programs.index');
//         Route::get('/programs/{id}', [\App\Http\Controllers\Institute\ProgramController::class, 'show'])->name('programs.show');

//         Route::get('/students', [InstituteStudentController::class, 'index'])->name('students.index');
//         Route::get('/students/create', [InstituteStudentController::class, 'create'])->name('students.create');
//         Route::post('/students', [InstituteStudentController::class, 'store'])->name('students.store');
//         Route::post('/students/import', [InstituteStudentController::class, 'import'])->name('students.import');
//         Route::get('/students/download-template', [InstituteStudentController::class, 'downloadTemplate'])->name('students.downloadTemplate');
//         Route::get('/students/export', [InstituteStudentController::class, 'export'])->name('students.export');
//     });

Route::post('admin/users/store-from-institute', [App\Http\Controllers\Admin\UserController::class, 'storeFromInstitute'])
    ->name('admin.users.store-from-institute')
    ->middleware('auth', 'role:admin');
Route::post('/admin/clear-cache', function () {
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');

    return back()->with('success', 'All caches cleared successfully!');
})->name('admin.clear.cache')->middleware('auth');

