<?php

session_start(); //Para iniciar o reanudar una sesion en la pagina, esto sirve solamente en esta seccion, si voy a otra no a menos que tenga el session start, pero si refresco si se mantiene 

require_once('../config/conexion.php'); //Hace que se ejecute una vez solamente el archivo de configuracion de la BD

if ($_SERVER['REQUEST_METHOD'] == 'POST') { //Si la solicitud es atraves del metodo post, entraos en este bloque
    
    $usser = $_POST['user_login'];  //Hago una variable local usuario, que sea igual al email introducido en el html
    $password = $_POST['password']; //idem, saco la clave de mi variable (array), y la almaceno en una variable

    $stmt = $conexion->prepare("SELECT usuario_login, contraseña, id_rol FROM usuarios WHERE usuario_login = ?"); //Secuencia para preparar la consulta de MYSQL (traer de la tabla usuarios [BD] el id, el email y la clave), se hace para mas seguridad, el signo hace que busque unicamente el valor que le voy a dar abajo
    $stmt->bind_param("s", $usser); //traer el string del email del usuario introducido en el html, almacenado en $usser
    $stmt->execute(); //Ejecutamos la consulta, para verificar que exista un email similar en la bd
    $result = $stmt->get_result();

    if($result->num_rows > 0) { //aca verifico si existe el usuario en la base de datos, cuantas filas me devolvio con ese usser, como es mas de 0 existe
        $row = $result->fetch_assoc(); //Transformo el resultado de ese usuario en un diccionario , con solamente los datos seleccionados, de su consulta
        $hash_password = $row['contraseña']; //hago una variable que es la contraseña encriptada de mi db, usando el elemento clave de mi columna

        if(password_verify($password, $hash_password)) { //funcion para desencriptar la clave hash y compararla con la del usuario

            $_SESSION['usser'] = $usser;  //Creacion de una variable de sesion, esta hace que el usuario de la seccion actual sea igual al usuario ingresado en email, esta es una variable que almacena los datos del usuario mientras navegue
            $_SESSION['id_rol'] = $row['id_rol'];   //Hago que el id del rol del usuario (evualuador estudiante y eso) sea el mismo que esta en la columna , usando mi diccionario
            if ($_SESSION['id_rol'] == 3) {
                header("Location: ../admin/dashboard.php"); //redirigo a el dashboard si todo esta bien
                exit();
            }
           
        } else{
            header("Location: ../index.php");
            exit();
        }
    } else {
        header("Location: ../index.php");
        exit();
    }   

    


} else { //pa que no entren por el link si no inician sesion 

    header("Location: ../index.php");
    exit();
}


?>