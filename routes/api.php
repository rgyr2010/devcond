<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\FounAndLostController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WarningController;
use App\Models\FoundAndLost;

Route::get('/401',[AuthController::class,'unauthorized'])->name('login');

Route::post('/auth/login',[AuthController::class,'login']);
Route::post('auth/register',[AuthController::class,'register']);

Route::middleware('auth:api')->group(function(){
Route::post('/auth/validate', [AuthController::class,'validateToken']);
Route::post('/auth/logout'  ,[AuthController::class,'logout']);

//Mural de avisos
Route::get('/walls',[WallController::class, 'getAll']);
Route::post('/walls/{id}/like',[WallController::class,'like']);

//documentos
Route::get('/docs',[DocController::class , 'getAll']);

//Livro de ocorrencia
Route::get('/warning',[WarningController::class,'getMyWarning']);
Route::post('/warning',[WarningController::class,'setMyWarning']);
Route::post('/warning/file',[WarningController::class,'addWarningFile']);

//boletos
Route::post('/billets',[BilletController::class,'getAll']);

//achados e perdidos
Route::get('/foundandlost',[FounAndLostController::class,'getAll']);
Route::post('/foundandlost',[FounAndLostController::class,'insert']);
Route::post('/foundandlost/{id}',[FounAndLostController::class,'update']);


//Unidade
Route::get('/unit/{id}',[UnitController::class,'getInfo']);
Route::post('/unit/{id}/addperson',[UnitController::class,'addPerson']);
Route::post('/unit/{id}/addvehicle',[UnitController::class,'AddVehicle']);
Route::post('/unit/{id}/addpets',[UnitController::class,'AddPet']);


//reserva
Route::get('/reservation',[ReservationController::class,'getReservation']);
Route::post('/reservation/{id}',[ReservationController::class,'setReservation']);

Route::post('/reservation/{id}/removepet',[ReservationController::class,'removePet']);
Route::get('/reservation/{id}/disabletddates',[ReservationController::class,'getDisabledDates']);
Route::get('/myreservation',[ReservationController::class,'getMyReservation']);
Route::get('/reservation/{id}/times',[ReservationController::class,'getTimes']);
Route::delete('/myreservation/{id}',[ReservationController::class,'delMyreservation']);


});
