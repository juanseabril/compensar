<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;


class ListaMedicosAtencion extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function lista_medicos($IDPaciente, $TIDPaciente, $NumeroAutorizacion, $Programa)
    {

        // Consulta mostrar por tipo_servicio
        $sql = "SELECT * FROM tb_param_autorizacion AS pa
        WHERE 
        /* pa.tipo_servicio ='CE' AND */
        pa.clinica_id = 1 ";

        $resultado = DB::connection('pgsql')->select($sql);

        if (sizeof($resultado) > 0) {

            //Cargo variable globla desde donde se almacena la url 
            //Ruta de archivo config/app/

            $url = config('app.url_WSCOMEPS') . '/AdministrarCita?sApl=::sApl&sXml=';
            $url = str_replace("::sApl", $resultado[0]->sapl, $url);

            dd($url);
            //Armo el sXml

            $sXml = "
            <AdministrarCita>
            <Encabezado>
            <GlnIPS>{$resultado[0]->nit}</GlnIPS>
            <GlnEPS>{$resultado[0]->codigo_compensar}</GlnEPS>
            <GlnPuntoAtencion>{$resultado[0]->direccion}</GlnPuntoAtencion>
            <TIDPaciente>{$TIDPaciente}</TIDPaciente>
            <IDPaciente>{$IDPaciente}</IDPaciente> 
            <NumeroAutorizacion>$NumeroAutorizacion</NumeroAutorizacion>
            <Programa>{$Programa}</Programa>
            <Opcion>3</Opcion>
            </Encabezado>
            </AdministrarCita>";

            // Sustituyo caracteres para eliminacion de espacios en blanco 
            $var_limpia = preg_replace("[\n|\r|\n\r]", "", $sXml);
            //dd(str_replace("        ", "", $var_limpia));

            // Reamplazo de espacios en blanco
            $var_limpia = str_replace("        ", "", $var_limpia);

            //Concateno la url con el sXml ya limpio(sin espacios en blanco)
            $url = $url . $var_limpia;

            //Hago la solicitud o peticion para comunicarme con la url
            $res = Http::withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
                'Access-Control-Allow-Methods' => ' GET, POST, PUT, DELETE',
                'UserName' => '9A2LLNCVHOzqHPTx+ThPEw==-03',
                'UserPass' => '+cnUaekk/Vb0LCxSz4QWKA==-03'
            ])
                ->withOptions([
                    'verify' => true,
                ])->get($url);

            //Obtengo el cuerpo de la respuesta mediante el metodo getBody y lo almaceno en la variable $body
            $body = $res->getBody();

            $xml_response = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
            $xml = simplexml_load_string($xml_response);

            //Insert de datos que devuelve el servicio web
            $sql_insert_incumplida = ("INSERT INTO tb_data_cita_incumplida (
                GlnEPS,
                GlnIPS,
                GlnPuntoAtencion,
                TIDPaciente,
                IDPaciente,
                NumeroAutorizacion,
                Nombre,
                Opcion,
                Observaciones,
                Mensaje,
                EstadoCita
                )
                 VALUES 
                 (               
                     '" . $xml->AdministraCita->GlnEPS . "',
                     '" . $xml->AdministraCita->GlnIPS . "',
                     '" . $xml->AdministraCita->GlnPuntoAtencion . "',
                     '" . $xml->AdministraCita->TIDPaciente . "',
                     '" . $xml->AdministraCita->IDPaciente . "',
                     '" . $xml->AdministraCita->NumeroAutorizacion . "',
                     '" . $xml->AdministraCita->Nombre . "',
                     '" . $xml->AdministraCita->Opcion . "',
                     '" . $xml->AdministraCita->Observaciones . "',
                     '" . $xml->AdministraCita->Mensaje . "',
                     '" . $xml->AdministraCita->EstadoCita . "' 
                 )
                 ");

            DB::connection('pgsql')->insert($sql_insert_incumplida);

            //Imprimo en formato xml que trae la variable $xml
            echo $xml_response; //->asXML();

        } else {

            return new JsonResponse("Solicitud no Encontrada!!!");
        }

        //dd($resultado);
    }
}