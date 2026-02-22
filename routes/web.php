<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Artisan;

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
    //Artisan::call('schedule:run');
	return view('welcome');
});
//Route::get('/link', [LinkController::class, 'checkLink']);
//Route::get('/link2', [LinkController::class, 'scrapeData']);
//Route::get('/link3', [LinkController::class, 'scrapeData2']);
//Route::get('/link4', [LinkController::class, 'MyScrap']);
//Route::get('/link5', [LinkController::class, 'createByLink']);
Route::get('/getLinks', [LinkController::class, 'getLinks']);
Route::get('/insertJobs', [LinkController::class, 'insertJobs']);

Route::get('/getLinksAngola', [LinkController::class, 'getLinksAngola']);
Route::get('/insertJobsAngola', [LinkController::class, 'insertJobsAngola']);
Route::get('/PublicarRascunho', [LinkController::class, 'PublicarRascunho']);
Route::get('/PublicarVagasDoDia', [LinkController::class, 'PublicarVagasDoDia']);

Route::get('/ObterDrafts', [LinkController::class, 'ObterDrafts']);
Route::get('/ObterEmpregosYoyotaAngoEmprego', [LinkController::class, 'ObterEmpregosYoyotaAngoEmprego']);
Route::get('/ObterAngolaEmpregoAngoEmprego', [LinkController::class, 'ObterAngolaEmpregoAngoEmprego']);
Route::get('/ObterAngolaEmpregoAngoEmprego/{website}', [LinkController::class, 'ObterAngolaEmpregoAngoEmprego']);

Route::get('/TituloVagaViaGemini', [LinkController::class, 'TituloVagaViaGemini']);

Route::get('/PublicarFacebook', [LinkController::class, 'PublicarFacebook']);

Route::get('/PublicarLinkedin', [LinkController::class, 'PublicarLinkedin']);
