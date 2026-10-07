<?php

use App\Http\Controllers\AttributionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\InternshipOfferController as AdminInternshipOfferController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\InternshipApplicationController as AdminInternshipApplicationController;
use App\Http\Controllers\Admin\InternshipController as AdminInternshipController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\InternshipOfferController;
use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\StudentInternshipController;
use App\Http\Controllers\LevelsController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\StudentController;
use App\Mail\HelloMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    return view('welcome');
});

Route::get('/offres-stage', [InternshipOfferController::class, 'index'])->name('internships.index');
Route::post('/offres-stage/{offer}/postuler', [InternshipApplicationController::class, 'store'])
    ->middleware('auth')
    ->name('internships.apply');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', AdminDashboardController::class)
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard/statistics', [AdminDashboardController::class, 'liveStatistics'])->name('dashboard.statistics');
    Route::get('applications', [AdminInternshipApplicationController::class, 'index'])->name('applications.index');
    Route::patch('applications/{application}/status', [AdminInternshipApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::get('internships', [AdminInternshipController::class, 'index'])->name('internships.index');
    Route::get('internships/{internship}', [AdminInternshipController::class, 'show'])->name('internships.show');
    Route::patch('internships/{studentInternship}/observation', [AdminInternshipController::class, 'updateObservation'])
        ->whereNumber('studentInternship')
        ->name('internships.observation.update');
    Route::patch('internships/{studentInternship}/dates', [AdminInternshipController::class, 'updateDates'])
        ->whereNumber('studentInternship')
        ->name('internships.dates.update');
    Route::resource('companies', AdminCompanyController::class);
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('offers', AdminInternshipOfferController::class)->except(['show']);
    Route::resource('students', AdminStudentController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/mon-stage', [StudentInternshipController::class, 'show'])->name('student.internship.show');
    Route::patch('/mon-stage/taches/{task}/avancement', [StudentInternshipController::class, 'updateTaskProgress'])
        ->whereNumber('task')
        ->name('student.internship.tasks.update');

    Route::get('/mes-candidatures', [InternshipApplicationController::class, 'index'])->name('applications.index');
    Route::get('/mes-candidatures/{application}', [InternshipApplicationController::class, 'show'])->name('applications.show');
    Route::delete('/mes-candidatures/{application}', [InternshipApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('/mon-cv', [CvController::class, 'index'])->name('cv.index');
    Route::post('/mon-cv', [CvController::class, 'store'])->name('cv.store');
    Route::get('/mon-cv/telecharger', [CvController::class, 'download'])->name('cv.download');
    Route::post('/mon-cv/analyser', [CvController::class, 'analyze'])->name('cv.analyze');
    Route::delete('/mon-cv', [CvController::class, 'destroy'])->name('cv.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('niveaux')->group(function () {
        Route::get('/', [LevelsController::class, 'index'])->name('niveaux');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SchoolYearController::class, 'index'])->name('settings');
        Route::get('/create-school-year', [SchoolYearController::class, 'create'])->name('settings.create_school_year');

        Route::get('/create-level', [LevelsController::class, 'create'])->name('settings.create_levels');

        Route::get('/edit-level/{level}', [LevelsController::class, 'edit'])->name('settings.edit_level');
    });

    Route::prefix('classes')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('classes');
        Route::get('/create', [ClassController::class, 'create'])->name('classes.create');
        Route::get('/edit/{classe}', [ClassController::class, 'edit'])->name('classes.edit');
    });

    //ROutes pour intérragir avec les élèves

    Route::prefix('eleves')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students');
        Route::get('/create', [StudentController::class, 'create'])->name('students.create');
        Route::get('/edit/{student}', [StudentController::class, 'edit'])->name('students.edit');
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');
    });

    Route::prefix('inscriptions')->group(function () {
        Route::get('/', [AttributionController::class, 'index'])->name('inscriptions');
        Route::get('/create', [AttributionController::class, 'create'])->name('inscriptions.create');
        Route::get('/edit/{attribution}', [AttributionController::class, 'edit'])->name('inscriptions.edit');
    });

    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payments');
        Route::get('/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::get('/edit/{payment}', [PaymentController::class, 'edit'])->name('payments.edit');
    });


    Route::prefix('parents')->group(function () {
        Route::get('/', [ParentController::class, 'index'])->name('parents');
        Route::get('/create', [ParentController::class, 'create'])->name('parents.create');
    });


    Route::prefix('frais')->group(function () {
        Route::get('/', [FeesController::class, 'index'])->name('fees');
        Route::get('/create', [FeesController::class, 'create'])->name('fees.create');
        Route::get('/edit/{fee}', [FeesController::class, 'edit'])->name('fees.edit');
    });
});

require __DIR__ . '/auth.php';
