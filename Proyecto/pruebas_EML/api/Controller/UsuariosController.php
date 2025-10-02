<?php

/**
 * Se genera la respectiva conexion.
 */

require_once __DIR__ . "/../Db/db_connect.php";

/**
 * Gestiona todas las operaciones de la base de datos para los usuarios (CRUD).
 *
 * Esta clase contiene los métodos para crear, leer, actualizar y eliminar
 * usuarios, interactuando directamente con la base de datos a través de PDO.
 */

class UsuariosController
{
    public PDO $db;
    public function __construct(PDO $Base)
    {
        $this->db = $Base;
    }

      /**
     * Crea un nuevo usuario en la base de datos tras validar que el email no exista.
     *
     * @param string $nombre   Los nombres del usuario.
     * @param string $apellido Los apellidos del usuario.
     * @param string $email    El correo electrónico del usuario (debe ser único).
     * @param int    $celular  El número de teléfono del usuario.
     * @param string $estado   El estado inicial del usuario ('activo' o 'inactivo').
     *
     * @return int Devuelve el ID del usuario recién creado.
     * @throws Exception Si el correo electrónico ya está registrado en la base de datos.
     */

    public function create_User(string $nombre, string $apellido, string $email, int $celular, string $estado): int
    {
        $validacion = $this->Obtener_Email($email);

        if ($validacion) {

            throw new Exception("El correo ya existe");

            return (0);
        }

        $result = $this->db->prepare("INSERT INTO usuarios (nombres, apellidos, email,telefono,estado) VALUES (?,?,?,?,?)");

        $result->execute([$nombre, $apellido, $email, $celular, $estado]);

        return (int)$this->db->lastInsertId();
    }

     /**
     * Actualiza los datos de un usuario existente en la base de datos.
     *
     * @param string $nombre   Los nuevos nombres del usuario.
     * @param string $apellido Los nuevos apellidos del usuario.
     * @param string $email    El nuevo correo electrónico del usuario.
     * @param int    $celular  El nuevo número de teléfono del usuario.
     * @param string $estado   El nuevo estado del usuario.
     * @param int    $id       El ID del usuario que se va a actualizar.
     *
     * @return bool Devuelve `true` si la actualización fue exitosa, `false` en caso contrario.
     */

    public function Update_User(string $nombre, string $apellido, string $email, int $celular, string $estado, int $id): bool
    {
        $result = $this->db->prepare("UPDATE usuarios SET nombres = ?,apellidos = ?,email= ?,telefono = ?,estado = ? WHERE id = ?");

        return $result->execute([$nombre, $apellido, $email, $celular, $estado, $id]); //se adiciona estado para su validacion en campo 
    }

    /**
     * Elimina un usuario de la base de datos utilizando su ID.
     *
     * @param int $id El ID del usuario a eliminar.
     *
     * @return bool Devuelve `true` si la eliminación fue exitosa, `false` en caso contrario.
     */

    public function Delete_User(int $id): bool
    {

        $result = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");

        return $result->execute([$id]);
    }

    /**
     * Obtiene la lista completa de todos los usuarios registrados.
     *
     * Los usuarios se devuelven ordenados alfabéticamente por sus nombres.
     *
     * @return array|null Un array de arrays asociativos con todos los usuarios, o `null` si la tabla está vacía.
     */

    public function Listar_User(): ?array
    {

        $result = $this->db->query("SELECT id,nombres,apellidos,email,telefono,estado,fecha_de_registro,fecha_de_ultima_modificacion FROM usuarios ORDER BY nombres ASC");

        $data = $result->fetchAll();

        return $data;
    }

    public function Obtener_User($id): ?array
    {

        $result = $this->db->prepare("SELECT id,nombres,apellidos,email,telefono,estado,fecha_de_registro,fecha_de_ultima_modificacion FROM usuarios WHERE id=?");

        $result->execute([$id]);

        $row = $result->fetch();

        return $row;
    }

     /**
     * Verifica si un correo electrónico ya existe en la base de datos.
     *
     * Este es un método de ayuda para prevenir la duplicación de usuarios.
     *
     * @param string $email El correo electrónico a verificar.
     *
     * @return bool Devuelve `true` si el email ya existe, `false` en caso contrario.
     */


    public function Obtener_Email(string $email): bool
    {
        $result = $this->db->prepare("SELECT 1 FROM usuarios WHERE email = ? LIMIT 1");
        $result->execute([$email]);

        $encontrado = $result->fetchColumn();
  
        return ($encontrado !== false);
    }
}
