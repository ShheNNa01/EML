create database Registro_Usuarios;

use Registro_Usuarios;

CREATE TABLE usuarios (
id INT auto_increment primary KEY,
nombres VARCHAR(100) NOT NULL,
apellidos VARCHAR(100) NOT NULL,
email VARCHAR (50) not null,
telefono VARCHAR (50) NOT NULL,
estado ENUM('activo','inactivo') DEFAULT 'activo',
fecha_de_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
fecha_de_ultima_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
