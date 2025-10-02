<?php
/*
  Este archivo es el "controlador de tráfico" de toda la aplicación.
  Es el único archivo PHP al que llama el JavaScript. Su trabajo es
  mirar la URL que le piden, ver qué acción quieren hacer (listar, crear, etc.)
  y llamar a la función correcta del UsuariosController para que haga el trabajo.
*/


/*
  Aquí traemos los archivos que necesitamos para que todo funcione:
  - El que se conecta a la base de datos (db_connect.php).
  - El que tiene todas las funciones para manejar usuarios (UsuariosController.php).
*/
require_once __DIR__ . "/../api/Db/db_connect.php";
require_once __DIR__ . "/../api/Controller/UsuariosController.php";

/*
  Creamos el "objeto controlador", que es como contratar a un ayudante
  que ya sabe cómo hablar con la base de datos. Le pasamos la conexión
  para que pueda empezar a trabajar.
*/
$ctrlUsuarios = new UsuariosController($Base);


// 2. ENTENDER QUÉ QUIERE HACER EL USUARIO

/*
  Revisamos qué tipo de petición nos están haciendo (GET para pedir datos,
  POST para crear, PUT para actualizar, DELETE para borrar). También
  miramos la URL para saber a qué se refieren (ej: "/usuarios").
*/
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');



// 3. DECIDIR QUÉ ACCIÓN TOMAR (El "Router")


/*
  RUTA: /usuarios (Método GET)
  Si nos piden la ruta "/usuarios" y están usando el método GET,
  significa que quieren la lista completa de todos los usuarios.
  Llamamos a la función Listar_User() y se la devolvemos.
*/
if ($path === '/usuarios' && $method === 'GET') {
  echo json_encode($ctrlUsuarios->Listar_User());
  exit;
}

/*
  RUTA: /usuarios (Método POST)
  Si nos piden la ruta "/usuarios" pero con el método POST,
  significa que quieren crear un nuevo usuario. Tomamos los datos
  que nos envían, llamamos a create_User() y si todo sale bien,
  devolvemos el ID del nuevo usuario. Si el correo ya existe,
  capturamos el error y avisamos (aca tuve que investigar y preguntar mucho, por lo que agradezco el reto).
*/
if ($path === '/usuarios' && $method === 'POST') {
  try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $ctrlUsuarios->create_User($data['nombres'], $data['apellidos'], $data['email'], $data['telefono'], $data['estado']);
    http_response_code(201);
    header('Content-Type: application/json');
    echo json_encode(['id' => $id]);
  } catch (Exception $e) {
    http_response_code(409);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
  }
  exit;
}

/*
  RUTA: /usuarios/ (Métodos GET, PUT, DELETE)
  Esta parte es para acciones que afectan a un solo usuario.
  Revisa si la URL tiene el formato "/usuarios/" seguido de un número.
  Si es así, agarra ese número (el ID) y decide qué hacer.
*/
if (preg_match('#^/usuarios/(\d+)$#', $path, $m)) {
  $id = (int)$m[1];

  // Si el método es GET, quieren los datos de ESE usuario.
  if ($method === 'GET') {
    echo json_encode($ctrlUsuarios->Obtener_User($id));
    exit;
  }
  // Si el método es PUT, quieren actualizar los datos de ESE usuario.
  if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ok = $ctrlUsuarios->Update_User($data['nombres'], $data['apellidos'], $data['email'], $data['telefono'], $data['estado'], $id);
    echo json_encode(['updated' => $ok]);
    exit;
  }
  // Si el método es DELETE, quieren borrar a ESE usuario.
  if ($method === 'DELETE') {
    $ok = $ctrlUsuarios->Delete_User($id);
    echo json_encode(['deleted' => $ok]);
    exit;
  }
}

/*
  Si después de revisar todas las rutas de arriba ninguna coincide,
  significa que nos pidieron una URL que no existe. Les devolvemos
  un error de "Ruta no encontrada".
*/
http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada']);
