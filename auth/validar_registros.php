<?php
require_once('../config/conexion.php');


if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = limpiar_inputs($_POST[''])

    if ($name === '' || $user === '' || $password === '') { //Verifico que ninguno de los tres campos sea igual a un espacio en blanco, con el triple igual hago una comparacion estricta
        echo"Dejaste un campo en blanco, vuelva a intentar <br>";
        echo"<a href='registro.html#registrarse'>Volver al registro de usuarios</a>";
        exit();
    }

    if (!filter_var($user, FILTER_VALIDATE_EMAIL)) { //Utilizio una funcionn de php para validar que tenga un formato email valido, y al usar el operador not, si no es valido manda pa atras
        echo"Formato de correo invalido, vuelva a intentar <br>";
        echo"<a href='registro.html#registrarse'>Volver al registro de usuarios</a>";
        exit();
    }

    if (strlen((string)$password) < 8) {
        echo"Longitud de contraseña menor a 8, vuelva a intentar <br>";
        echo"<a href='registro.html#registrarse'>Volver al registro de usuarios</a>"; //a href es una etiqueta para crear un enlace, lo otro que tiene es a donde se va a redirigir al hacer clic al tocar el mensaje del final 
        exit();
    }

    

    $crypted_password = (password_hash($password, PASSWORD_DEFAULT)); //Creacion de la contraseña encriptada


    $stmt = $conexion->prepare("SELECT email FROM usuarios WHERE email =? "); //Preapracion de la consulta a la BD
    $stmt->bind_param("s", $user); //Vinculando el parametro desconocido con el usuario, ingresado por html
    $stmt->execute(); //Ejecucion de la consulta
    $result = $stmt->get_result(); //Se obtiene el resultado

    if ($result->num_rows > 0 ) { //Consulta para verificar si el  email existe

        echo"Este email ya existeeeee, prueba con otro <br>";
        echo"<a href='registro.html#registrarse'>Volver al registro de usuarios</a>";
        exit();
    } else { //si no existe

        $stmt->close(); //Cierro la consulta, para poder liberar memoria y hacer otras consultas

        $factoruser = "USER-REG-" . $factor;

        $insert_stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, clave, codigo_identificador) VALUES (?, ?, ?, ?)"); //preparando la insercion de valores en la tabla usuarios, en los campos nombre email, clave y el codigo, valores desconocidos por seguridad
        $insert_stmt->bind_param("ssss", $name, $user, $crypted_password, $factoruser); //Vinculo los valores (triple string)

        if ($insert_stmt->execute()) { //Si se guardan los valores exitosamente
            echo "Usuario registrado exitosamente, ya puedes iniciar sesion <br> ";
            echo "<a href='index.html#login'>Ir al Login</a>";
            $insert_stmt->close();
            exit();
        } else { //sino
            echo "Error al registrar el usuario. Intenta de nuevo más tarde.<br>";
            echo "<a href='registro.html#registrarse'>Volver al registro</a>";
            $insert_stmt->close();
            exit();
        }

        

    }

    


} else {
    header("Location: registro.html");
    exit();
}

?>


