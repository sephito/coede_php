<?php



require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', 'root', 'coede_covid');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


 


$app->get("/pruebas", function() use($app, $db){
	echo "Hola mundo desde Slim PHP";
});

$app->get("/probando", function() use($app){
	echo "OTRO TEXTO CUALQUIERA";
});

// LISTAR TODOS LOS PRODUCTOS
$app->get('/registros', function() use($db, $app){
	$sql = 'SELECT * FROM registro ORDER BY id DESC;';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

// DEVOLVER UN SOLO REGISTRO
$app->get('/registro/:id', function($id) use($db, $app){
	$sql = 'SELECT * FROM registro WHERE cip = '.$id;
	$query = $db->query($sql);

	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'usuario no disponible'
	);

	if($query->num_rows == 1){
		$registro = $query->fetch_assoc();

		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'data' 	=> $registro
		);
	}

	echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get('/delete-producto/:id', function($id) use($db, $app){
	$sql = 'DELETE FROM productos WHERE id = '.$id;
	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El producto se ha eliminado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El producto no se ha eliminado!!'
		);
	}

	echo json_encode($result);
});

// ACTUALIZAR UN PRODUCTO
$app->post('/update-registro/:id', function($id) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	$sql = "UPDATE registro SET ".
		   "lat = {$data["lat"]}, ".
		   "lng = {$data["lng"]} ";

	

	$sql .=	" WHERE cip = '{$id}'";


	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El registro se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El registro no se ha actualizado!!'
		);
	}

	echo json_encode($result);

});

// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'El archivo no ha podido subirse'
	);

	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});

// GUARDAR PRODUCTOS
$app->post('/registro', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(!isset($data['cip'])){
		$data['cip']=null;
	}

	if(!isset($data['clave'])){
		$data['clave']=null;
	}

	if(!isset($data['lat'])){
		$data['lat']=null;
	}

	if(!isset($data['lng'])){
		$data['lng']=null;
	}

	$query = "INSERT INTO registro VALUES(NULL,'$data[cip]','$data[clave]','$data[lat]','$data[lng]',NOW() + 1)";

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'registro NO se ha creado'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'registro creado correctamente'
		);
	}

	echo json_encode($result);
});

$app->run();