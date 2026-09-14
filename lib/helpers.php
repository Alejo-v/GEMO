<?php
    //session_start();
    function redirect($url){
        echo "<script>";
        echo "window.location.href='$url'";
        echo "</script>";
    }
    function dd($data){
        echo "<pre>";
        die(print_r($data));
    }
    function getUrl($modulo, $controlador, $funcion, $parametros = false,$pagina = false){

        if($pagina == false){
            $pagina = "index";
        }

        $url = "$pagina.php?modulo=$modulo&controlador=$controlador&funcion=$funcion";

        if($parametros != false){
            foreach($parametros as $key => $valor){
                $url .= "&$key=$valor";
            }
        }
        return $url;
    }

    function resolve(){
        $modulo = ucwords($_GET['modulo']); //Carpeta ej: Usuarios
        $controlador = ucwords($_GET['controlador']); //Archivo ej: UsuariosController.php
        $funcion = $_GET['funcion']; //Metodo en la clase: getUsers

        if(is_dir("../controller/$modulo")){ //is_dir para verificar si es una carpeta

            if(is_file("../controller/$modulo/".$controlador."Controller.php")){

                include_once "../controller/$modulo/$controlador"."Controller.php";
                $nombreClase = $controlador."Controller";

                $objeto = new $nombreClase(); //$objeto = new UsuariosController();

                if(method_exists($objeto,$funcion)){
                    $objeto->$funcion();
                }else{
                    echo "El metodo $funcion no existe en el controlador $controlador";
                }
            }else{
                echo "El controlador $controlador no existe en el modulo $modulo";
            }
        }else{
            echo "El modulo $modulo no existe";
        }
    }
?>