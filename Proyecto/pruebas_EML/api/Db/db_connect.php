<?php
/**
 * Script de conexión a la base de datos MySQL utilizando PDO.
 */

$host = 'localhost';
$port = '3307'; // Dado que mi pc tenia utilizado el puerto 3306, se realiza cambio al puerto mencionado para ejecucion.
$db_name = 'registro_usuarios';
$username = 'root';
$password = '';

try {
    $Base = new PDO("mysql:host=$host;port=$port;dbname=$db_name;",$username,$password);

    $Base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $exception) {
    echo "Error de conexión: " . $exception->getMessage();
    exit();
}
