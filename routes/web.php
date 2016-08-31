<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

// Static Pages
Route::get('/', 'PagesController@getLandingMain');

// Checklists
Route::get('/checklist', 'ChecklistsController@getListsView');
Route::get('/checklist/get', 'ChecklistsController@getForAuthenticatedUser');
Route::get('/checklist/make', 'ChecklistsController@getMakeForm');
Route::post('/checklist/make', 'ChecklistsController@postNewChecklist');
Route::get('/checklist/{checklist_hash}', 'ChecklistsController@getSingleChecklist');
Route::get('/checklist/{checklist_hash}/files', 'ChecklistsController@getFilesForChecklist');
Route::post('/checklist/{checklist_hash}/turn_off_notifications', 'ChecklistsController@postTurnOffNotifications');

// Files
Route::post('/file/{fileRequest}', 'FilesController@postUploadFile');
Route::post('/file/{fileRequest}/reject', 'FilesController@postRejectUploadedFile');

// Account
Route::get('/account', 'AccountController@getAccountOverview');
Route::post('/account/subscription', 'AccountController@postSubscribe');
Route::delete('/account/subscription', 'AccountController@deleteCancelSubscription');
Route::post('/account/subscription/resume', 'AccountController@postResumeSubscription');

