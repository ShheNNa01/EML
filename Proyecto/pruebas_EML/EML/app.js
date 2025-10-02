/*
  Este archivo se encarga de toda la parte interactiva de la página.
  Maneja las acciones como cargar la lista de usuarios, crear uno nuevo,
  editarlo y eliminarlo. Todo se comunica con el servidor (el código PHP)
  para guardar y obtener los datos.
*/

// INICIO DE LA APLICACIÓN

/*
  Esto es lo primero que se ejecuta cuando la página termina de cargar.
  Su única tarea es llamar a la función `cargarUsuarios` para que la tabla
  aparezca con los datos desde el primer momento.
*/
jQuery(document).ready(function($){
  cargarUsuarios(); 
});

// FUNCIONES PRINCIPALES (Las herramientas de nuestro código)

/*
  Esta función "dibuja" la tabla. Recibe una lista de usuarios
  y, por cada uno, crea una fila (un <tr>) en HTML con todos sus datos
  y los botones de acción. Al final, mete todas esas filas en la tabla.
*/
function renderTabla(lista) {
  let rows = "";

  lista.forEach(usuario => {
    rows += `
      <tr>
        <td>${usuario.id}</td>
        <td>${usuario.nombres}</td>
        <td>${usuario.apellidos}</td>
        <td>${usuario.email}</td>
        <td>${usuario.telefono}</td>
        <td><span class="badge ${usuario.estado === "activo" ? "bg-success" : "bg-danger"}">${usuario.estado}</span></td>
        <td>${usuario.fecha_de_registro}</td>
        <td>${usuario.fecha_de_ultima_modificacion}</td>
        <td>
          <button class="btn btn-sm btn-warning btn-editar" data-id="${usuario.id}">Editar</button>
          <button class="btn btn-sm btn-danger btn-eliminar" data-id="${usuario.id}">Eliminar</button>
        </td>
      </tr>
    `;
  });

  $("#tabla-body").html(rows);
}

/*
  Una función simple para limpiar el formulario de creación.
  Se llama después de guardar un usuario o si ocurre un error,
  para que los campos queden vacíos y listos para el siguiente.
*/
function limpiarFormularioCrear() {
  $('#formCrearUsuario')[0].reset();
}

/*
  Esta función se conecta con el servidor para traer la lista completa
  de usuarios. Una vez que los recibe, llama a `renderTabla` para
  mostrarlos en la página.
*/
function cargarUsuarios() {
  $.get("../Public/index.php?path=/usuarios", function (data) {
    renderTabla(data);
  },"json");
}


// ACCIONES DE LOS BOTONES (Qué pasa cuando el usuario hace clic en la app)

/*
  ACCIÓN: Guardar un usuario nuevo.
  Cuando se hace clic en el botón 'Guardar' del formulario de creación,
  este código se activa. Primero recoge los datos, luego los revisa
  (que no estén vacíos, que el email y teléfono sean válidos) y si todo
  está bien, los envía al servidor para que los guarde.
*/
$("#btnGuardarUsuario").click(function () {
  
  const usuario = {
    nombres: $("#nombres").val().trim(),
    apellidos: $("#apellidos").val().trim(),
    email: $("#email").val().trim(),
    telefono: $("#telefono").val().trim(),
    estado: $("#estado").val()
  };

  if (!usuario.nombres || !usuario.apellidos || !usuario.email || !usuario.telefono) {
    alert("Por favor, completa todos los campos.");
    return;
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(usuario.email)) {
    alert("Por favor, introduce un correo electrónico válido.");
    return;
  }
  const telefonoRegex = /^[0-9]+$/;
  if (!telefonoRegex.test(usuario.telefono)) {
    alert("El teléfono solo debe contener números.");
    return;
  }

  $.ajax({
    url: "../Public/index.php?path=/usuarios",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(usuario),
    success: function (response) {
      alert("Usuario creado con ID: " + response.id);
      $("#crearUsuarioModal").modal("hide");
      limpiarFormularioCrear();
      cargarUsuarios();
    },
    error: function (jqXHR, textStatus, errorThrown) {
      let mensajeError = "Ocurrió un error inesperado al crear el usuario.";
      if (jqXHR.responseText) {
        try {
          const respuesta = JSON.parse(jqXHR.responseText);
          if (respuesta && respuesta.error) {
            mensajeError = respuesta.error;
          }
        } catch (e) {
          console.error("La respuesta de error no es un JSON válido:", jqXHR.responseText);
        }
      }
      alert(mensajeError);

      limpiarFormularioCrear();

    }
  });
});

/*
  ACCIÓN: Abrir el formulario para editar.
  Este código está (escuchando) por si se hace clic en CUALQUIER botón
  con la clase 'btn-editar'. Cuando pasa, agarra el ID del usuario de ese botón,
  le pide al servidor los datos de ese usuario en específico y los pone
  en el formulario de edición antes de mostrarlo.
*/
$(document).on("click", ".btn-editar", function () {
  const id = $(this).data("id");

  $.get(`../Public/index.php?path=/usuarios/${id}`, function (user) {
    $("#edit-id").val(user.id);
    $("#edit-nombres").val(user.nombres);
    $("#edit-apellidos").val(user.apellidos);
    $("#edit-email").val(user.email);
    $("#edit-telefono").val(user.telefono);
    $("#edit-estado").val(user.estado);

    $("#editarUsuarioModal").modal("show");
  }, "json");
});

/*
  ACCIÓN: Guardar los cambios de un usuario editado.
  Cuando se da clic en 'Actualizar', este código recoge los nuevos datos
  del formulario de edición y los envía al servidor para que actualice
  la información de ese usuario en la base de datos.
*/
$("#btnActualizarUsuario").click(function () {
  const id = $("#edit-id").val();
  const data = {
    nombres: $("#edit-nombres").val(),
    apellidos: $("#edit-apellidos").val(),
    email: $("#edit-email").val(),
    telefono: $("#edit-telefono").val(),
    estado: $("#edit-estado").val()
  };

  $.ajax({
    url: `../Public/index.php?path=/usuarios/${id}`,
    type: "PUT",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function () {
      $("#editarUsuarioModal").modal("hide");
      limpiarFormularioCrear();
      cargarUsuarios(); // Refresca la tabla para ver los cambios
    }
  });
});

/*
  ACCIÓN: Eliminar un usuario.
  Al igual que el de editar, este código escucha por clics en los botones
  'btn-eliminar'. Pregunta si estás seguro, y si dices que sí, le manda
  la orden al servidor para que borre a ese usuario.
*/
$(document).on("click", ".btn-eliminar", function () {
  const id = $(this).data("id");

  if (confirm("¿Seguro que deseas eliminar este usuario?")) {
    $.ajax({
      url: `../Public/index.php?path=/usuarios/${id}`,
      type: "DELETE",
      success: function () {
        cargarUsuarios();
      }
    });
  }
});