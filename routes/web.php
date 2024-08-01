<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/teacher-portal', [StudentController::class, 'portal'])->name('teacher.portal');
    Route::post('/students/index', [StudentController::class, 'index'])->name('student.index');
    Route::post('/students', [StudentController::class, 'store'])->name('student.store');
    Route::patch('/student/{id}', [StudentController::class, 'update'])->name('student.update');
    Route::delete('/student/{id}', [StudentController::class, 'destroy'])->name('student.destroy');
    Route::get('/subjects', [StudentController::class, 'getSubjects']);
    Route::post('/currentMarks', [StudentController::class, 'getCurrentMarks'])->name('currentMarks');

});


