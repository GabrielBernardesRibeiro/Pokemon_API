<?php
	header("Access-Control-Allow-Origin: *"); //Coloque o dominios dos sites que podem consumir sua api
	header('content-type: application/json'); //Configurando o header para o retorno ser em json
	require_once 'vendor/autoload.php';

	if ( isset($_GET['url']) )
	{
		$url = explode('/', $_GET['url']);
		if ($url[0] === 'api')
		{
			array_shift($url);

			$controller = 'Pokemon_project\Controllers\\'.ucfirst($url[0]).'_controller';
			array_shift($url);

			try {
				$result_of_request = call_user_func_array(array(new $controller, 'get'), $url);
				http_response_code(200);
				echo json_encode(array(
					'status' => 'success',
					'data' => $result_of_request
				));
				exit;
			} catch (\Throwable $th) {
				http_response_code(404);
				echo json_encode(array(
					'status' => 'error',
					'data' => $th->getMessage()
				), JSON_UNESCAPED_UNICODE);
				exit;
			}
			
		}
	}

?>