<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
//use SimpleXMLElement;


class CAdministrarCita extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * AdministraCita Incumplir
     * https://pruws.compensarsalud.com/WSCOMEPS/SAS/SAS.asmx/AdministrarCita?sApl=CESTANCIA&sXml= Consumo por Rest
     *
     * @return \Illuminate\Http\Response
     */

    public function incumplida($IDPaciente, $TIDPaciente, $NumeroAutorizacion, $Programa)
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

    /**
     * AdministraCita Cancelada
     * https://pruws.compensarsalud.com/WSCOMEPS/SAS/SAS.asmx/AdministrarCita?sApl=CESTANCIA&sXml= Consumo por Rest
     *
     * @return \Illuminate\Http\Response
     */

    public function cancelada($IDPaciente, $TIDPaciente, $NumeroAutorizacion, $Programa)
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
            <Opcion>1</Opcion>
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
            $sql_insert_cancelada = ("INSERT INTO tb_data_cita_cancelada (
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

            DB::connection('pgsql')->insert($sql_insert_cancelada);

            //Imprimo en formato xml que trae la variable $xml
            echo $xml_response; //->asXML();

        } else {

            return new JsonResponse("Solicitud no Encontrada!!!");
        }

        //dd($resultado);
    }

    /**
     * AdministraCita Cumplida
     * https://pruws.compensarsalud.com/WSCOMEPS/SAS/SAS.asmx/AdministrarCita?sApl=CESTANCIA&sXml= Consumo por Rest
     *
     * @return \Illuminate\Http\Response
     */

    public function cumplida($IDPaciente, $TIDPaciente, $NumeroAutorizacion, $Programa)
    {

        // Consulta mostrar por tipo_servicio
        $sql = "SELECT * FROM tb_param_autorizacion AS pa
        WHERE 
        /* pa.tipo_servicio ='CE' AND */
        pa.clinica_id = 1 ";

        $resultado = DB::connection('pgsql')->select($sql);

        //dd($resultado);

        if (sizeof($resultado) > 0) {

            //Cargo variable globla desde donde se almacena la url 
            //Ruta de archivo config/app/

            $url = 'https://ws.compensarsalud.com/WSCOMEPS/SAS/SAS.asmx/AdministrarCita?sApl=::sApl&sXml=';
            $url = str_replace("::sApl", $resultado[0]->sapl, $url);

            //dd($url);

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
            <Opcion>2</Opcion>
            </Encabezado>
            </AdministrarCita>";

            // Sustituyo caracteres para eliminacion de espacios en blanco 
            $var_limpia = preg_replace("[\n|\r|\n\r]", "", $sXml);
            //dd(str_replace("        ", "", $var_limpia));

            // Reamplazo de espacios en blanco
            $var_limpia = str_replace("        ", "", $var_limpia);

            //Concateno la url con el sXml ya limpio(sin espacios en blanco)
            $url = $url . $var_limpia;

            //dd($url);

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
            // dd($xml->AdministraCita->Observaciones);

            $xml_response = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
            $xml = simplexml_load_string($xml_response);

            //Insert de datos que devuelve el servicio web
            $sql_insert_cumplida = ("INSERT INTO tb_data_cita_cumplida (
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

            DB::connection('pgsql')->insert($sql_insert_cumplida);

            //Imprimo en formato xml que trae la variable $xml
            echo $xml_response;
        } else {

            return new JsonResponse("Solicitud no Encontrada!!!");
        }

        //dd($resultado);
    }

    public function consulta_paciente_estado_cita($fecha)
    {

        $newDate = date("d/m/Y", strtotime($fecha));

        $fechaIni = $newDate . ' 00:00:00';
        $fechaFin = $newDate . ' 23:59:59';
        $sql = "SELECT
        cm.CITA_MEDICA,  
        cm.FECHA_CITA,
        cm.FECHA_INICIO_ATENCION,
        cm.HORA_CITA,
        cm.ESTADO,
        CITMED.CITESTP,
        cm.NUMEROAUTORIZACION,
        cm.TIPO_ID,cm.ID_PACIENTE, 
        cm.NOMBRESUSUARIO, 
        cm.APELLIDOSUSUARIO, 
        cm.GENERO, 
        cm.FECHA_NCTO, 
        cm.Edad, 
        cm.DIRECCION, 
        cm.EMAIL,
        cm.CELULAR, 
        MAEEMP.MENNIT,
        MAEEMP.MENOMB,
        MAEEMP.MEcntr,
        cm.IDENTIF_MEDICO,
        cm.CUPS,
        cm.PROGRAMA

        FROM View_Citas_Medico_Detallado_Depurado AS cm INNER JOIN
                                 MAEPRO ON cm.CUPS = MAEPRO.PRCODI INNER JOIN
                                 CITMED ON MAEPRO.PRCODI = CITMED.CitPro AND cm.NUMERO_CITA = CITMED.CitNum INNER JOIN
                                 MAEEMP ON cm.CONTRATO = MAEEMP.MENNIT INNER JOIN
                                 EMPRESS ON MAEEMP.MEcntr = EMPRESS.MEcntr
        WHERE (cm.CITEMP = '1') AND (cm.CITSED = '001') 
        AND (CITMED.CITESTP = 'R' OR CITMED.CITESTP = 'A' OR CITMED.CITESTP = 'N')
        AND (MAEEMP.MENNIT = '000159' OR MAEEMP.MENNIT = '000602' OR MAEEMP.MENNIT = '000756' OR MAEEMP.MENNIT = '000757' OR MAEEMP.MENNIT = '000653' OR MAEEMP.MENNIT = '000655' OR MAEEMP.MENNIT = '000656')
        AND (cm.FECHA_CITA BETWEEN '" . $fechaIni .  "' AND '" . $fechaFin .  "')";


        //Config(['database.connections.sqlsrv']);
        Config(['database.connections.sqlsrv' => config('app.conexion')]);
        $conexionSQL = DB::connection('sqlsrv');

        $resultado = $conexionSQL->select($sql);

        if (sizeof($resultado) > 0) {

            $array =
                array(
                    "ConsultarAfiliadoOut" =>
                    array(
                        "DatosBasicos" => $resultado
                    )
                );
        } else {

            $array =
                array(
                    "ConsultarAfiliadoOut" =>
                    array(
                        "Errores" => [
                            array(
                                "IdError" => "D0001",
                                "Error" => "No existe información"
                            )
                        ]

                    )
                );
        }
        //dd($array['ConsultarAfiliadoOut']['DatosBasicos'][0]['ID_PACIENTE']);
        DB::disconnect('sqlsrv');

        return new JsonResponse($array);
    }
}
