<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Ruta testeo
Route::get('index', 'consumo@index');

// Ruta ConsultarAfiliado
Route::get('consultar_afiliado/{IDAfiliado}/{TIDAfiliado}', 'CConsultarAfiliado@consultar_afiliado');

// Ruta Autorización
Route::get('autorizacion/{FechaNacimiento}/{TIDPaciente}/{IDPaciente}/{Servicio}/{Telefono}/{IDRemitente}/{Programa}/{NumIdentificarCita}', 'CAutorizacion@autorizacion');

// Ruta Cita Cumplida
Route::get('cita_cumplida/{IDAfiliado}/{TIDAfiliado}/{NumeroAutorizacion}/{Programa}', 'CAdministrarCita@cumplida');


// Ruta Cita Cancelada
Route::get('cita_cancelada/{IDAfiliado}/{TIDAfiliado}/{NumeroAutorizacion}/{Programa}', 'CAdministrarCita@cancelada');

// Ruta Cita Incumplida
Route::get('cita_incumplida/{IDAfiliado}/{TIDAfiliado}/{NumeroAutorizacion}/{Programa}', 'CAdministrarCita@incumplida');


// Ruta Update Tbl Hosvital
//{numero_cita}/{numero_autorizacion}/{clinica_id}
Route::patch('update_autorizacion/{numero}', 'consumo@update_autorizacion');

// Ruta Testeo
Route::get('index', 'consumo@index');
Route::get('capbas', 'consumo@capbas');

// Ruta Cita Incumplida
Route::get('consulta/{IDAfiliado}', 'CAdministrarCita@consulta');