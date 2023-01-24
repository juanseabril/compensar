<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class CConsultarAfiliado extends Controller
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
     * ConsultarAfiliado opción 1
     * https://pruws.compensarsalud.com/WConsultasSAS/AFIRest.svc Consumo por Rest
     *
     * @return \Illuminate\Http\Response
     */

    public function consultar_afiliado($IDAfiliado, $TIDAfiliado)
    {
        //Parametrizacion

        $CLINICA_ID = 1;
        $TIPO_SERVICIO = 'CE';

        $sql = "SELECT * FROM tb_param_autorizacion AS pa
        WHERE pa.clinica_id =" . $CLINICA_ID;

        /// datos reemplazo por los que retorna la consulta sql

        $resultado = DB::connection('pgsql')->select($sql);

        // dd($resultado);

        // $sql = "SELECT * FROM tb_param_autorizacion AS pa
        // WHERE 
        // /pa.tipo_servicio ='$TIPO_SERVICIO' AND/
        // pa.clinica_id =" . $CLINICA_ID;

        // /// datos reemplazo por los que retorna la consulta sql

        // $resultado = DB::connection('pgsql')->select($sql);

        //var_dump($resultado);

        //dd($resultado);
        //print_r($resultado[0]->sApl);
        //dd($id, $id2);

        // if (sizeof($resultado) > 0) {


        //'$user = 9A2LLNCVHOzqHPTx+ThPEw==-03';
        //$pwd = '+cnUaekk/Vb0LCxSz4QWKA==-03';

        //Cargo variable globla desde donde se almacena la url 
        //Ruta de archivo config/app/
        $url = config('app.url_WConsultasSAS')
            . '/ConsultarAfiliado?sApl=::sApl&IDAfiliado=::IDAfiliado&TIDAfiliado=::TIDAfiliado&CodEPS=::CodEPS&Opcion=1&Formato=JSON';

        //Wildcards
        //Capturamos el servicio en la variable $url, y pasamos los parametros.
        $url = str_replace("::IDAfiliado", $IDAfiliado, $url);
        $url = str_replace("::TIDAfiliado", $TIDAfiliado, $url);
        $url = str_replace("::sApl", $resultado[0]->sapl, $url);
        $url = str_replace("::CodEPS", $resultado[0]->codigo_compensar, $url);


        //Abrimos conexión cURL y la almacenamos en la variable $ch
        $ch = curl_init();

        //Configuramos mediante CURLOPT_URL la URL de nuestra API, en este caso pasamos la variable en donde esta la ruta
        curl_setopt($ch, CURLOPT_URL, $url);


        //Verificacion de certificado ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //Abrimos conexión cURL y la almacenamos en la variable $ch
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            'Access-Control-Allow-Origin: *',
            'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods: GET, POST, PUT, DELETE',
            'UserName:9A2LLNCVHOzqHPTx+ThPEw==-03',
            'UserPass:+cnUaekk/Vb0LCxSz4QWKA==-03'
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //Ejecuta la petición HTTP y almacena la respuesta en la variable $result.
        $result = curl_exec($ch);

        //retorna json el resultado de la variable $result y lo almacenamos en la variable $res
        $res = json_decode($result, TRUE);


        //Cerramos la conexión cURL
        curl_close($ch);

        //dd($res);

        //dd($res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion']);
        /**
         * Validacion de respuesta si la url retorna una respuesta o error 'D0001' 
         * quiere decir que no existe información con los datos consultados, pero si encontro al paciente
         * consultado valida su estado de vinculacion en donde si es 0 responde un true quiere decir que 
         * el paciente tiene una afiliacion, de lo contrario si es un numero diferente a 0 retorna una false
         */

        // dd($res['ConsultarAfiliadoOut']['PROGRAMA'][0]);

        if (isset($res['ConsultarAfiliadoOut']['Errores'][0]['IdError']) == 'D0001') {

            //return new JsonResponse('No existe información, con los datos consultados');

            return new JsonResponse($res);
        } elseif (isset($res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion'])) {

            //return new JsonResponse('false');

            if ($res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion'] != 0 && $res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion'] != 2) {

                $rta = [
                    "Code" => 200,
                    "Status" => "OK",
                    "Data" => "False",
                    "SMS" => "Usuario No Afiliado",
                    "Estado Vinculacion" => $res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion']



                ];
                //dd($res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion']);
                return new JsonResponse($rta);
            } elseif ($res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion'] == 0 || $res['ConsultarAfiliadoOut']['PROGRAMA'][0]['EstadoVinculacion'] == 2) {

                //Insert de datos que devuelve el servicio web
                $sql_consultar_afiliado_insert = ("INSERT INTO tb_data_afiliado (
            TIDAfiliado,
            IDAfiliado,
            Nuip,
            PrimerApellido,
            SegundoApellido,
            PrimerNombre,
            SegundoNombre,
            FecNacimiento,
            Edad,
            Genero,
            TelAfiliado,
            CelAfiliado,
            CorreoElectroAfiliado,
            DirAfiliado,
            Barrio,
            Localidad,
            Zona,
            CodCiudad,
            CodDepartamento,
            Estrato,
            EstadoCivil,
            CodParentesco,
            CODOCU,
            TipoUsuario,
            TipoAfiliado,
            Convenio,
            SemanasCotizadas,
            CodEPSAnterior,
            CodEPS,
            AutEnvioMensajeEmail,
            AutEnvioMensajeSMS,
            Nautcli,
            Alerta,
            FechaUltNovedad,
            Programa
            )
                VALUES 
                (
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['TIDAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['IDAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Nuip'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['PrimerApellido'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['SegundoApellido'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['PrimerNombre'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['SegundoNombre'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['FecNacimiento'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Edad'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Genero'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['TelAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CelAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CorreoElectroAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['DirAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Barrio'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Localidad'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Zona'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CodCiudad'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CodDepartamento'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Estrato'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['EstadoCivil'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CodParentesco'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CODOCU'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['TipoUsuario'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['TipoAfiliado'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Convenio'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['SemanasCotizadas'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CodEPSAnterior'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['CodEPS'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['AutEnvioMensajeEmail'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['AutEnvioMensajeSMS'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Nautcli'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['Alerta'] . "',
                    '" . $res['ConsultarAfiliadoOut']['DatosBasicos'][0]['FechaUltNovedad'] . "',
                    '" . $res['ConsultarAfiliadoOut']['PROGRAMA'][0]['Programa'] . "'
                
                    )
                    ");

                DB::connection('pgsql')->insert($sql_consultar_afiliado_insert);


                $rta = [
                    "Code" => 200,
                    "Status" => "OK",
                    "SMS" => "Usuario Vinculado",
                    "Data" => $res['ConsultarAfiliadoOut']['DatosBasicos'][0],
                    "Programa" => $res['ConsultarAfiliadoOut']['PROGRAMA']

                ];


                //echo $rta;

                //dd($res);

                //Llamo metodo update autorizacion testeo
                //$this->update_autorizacion(1, 1, 1);

                //print json_encode($rta);
                return new JsonResponse($rta);
                //return new JsonResponse('true');
            }


            //return new JsonResponse($res);
        } else {
            return new JsonResponse($res);
        }
            //echo $result;
            //}

            //dd($res);
            //var_dump($res);
        // } else {


        //     $rta = [
        //         "Code" => 200,
        //         "Status" => "OK",
        //         "Data" => "True",
        //         "SMS" => "Solicitud no Encontrada!!!"
        //     ];


        //     return new JsonResponse($rta);
        // }
    }
}