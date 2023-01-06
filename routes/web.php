<?php

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
    return redirect('/register');
});

Route::get('/tasks', 'App\Http\Controllers\TaskController@Index');
Route::get('/tasks_data', 'App\Http\Controllers\TaskController@Data');
Route::get('/create', 'App\Http\Controllers\TaskController@Create');
Route::post('/store', 'App\Http\Controllers\TaskController@Store');
Route::get('/tasks/{id}', 'App\Http\Controllers\TaskController@Show');
Route::put('/update-task/{id}', 'App\Http\Controllers\TaskController@Update');
Route::delete('/task/{id}', 'App\Http\Controllers\TaskController@Destroy')->name('task.destroy');
Route::get('priority_update', 'App\Http\Controllers\TaskController@PriorityUpdate');


// http://employee.demo/insta/callback