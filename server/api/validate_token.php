<?php
  // required headers
  header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  // respond to preflights
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
  {
    // return only the headers and not the content
    // only allow CORS if we're doing a GET - i.e. no saving for now.
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET')
    {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: X-Requested-With');
    }
    exit();
  }

  // required to decode jwt
  include_once 'config/core.php';
  include_once 'libs/php-jwt-master/src/BeforeValidException.php';
  include_once 'libs/php-jwt-master/src/ExpiredException.php';
  include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
  include_once 'libs/php-jwt-master/src/JWT.php';
  use \Firebase\JWT\JWT;

  // get posted data
  $data = json_decode(file_get_contents("php://input"));

  // get jwt
  $jwt = isset($data->jwt) ? $data->jwt : "";

  // if jwt is not empty
  if ($jwt)
  {
    // if decode succeed, show user details
    try
    {
      // decode jwt
      $decoded = JWT::decode($jwt, $key, array('HS256'));

      // set response code
      http_response_code(200);

      // show user details
      echo json_encode(array(
        "message" => "Access granted.",
        "data"    => $decoded->data,
      ));
    }
    // if decode fails, it means jwt is invalid
    catch (Exception $e)
    {
      // set response code
      http_response_code(401);

      // tell the user access denied  & show error message
      echo json_encode(array(
        "message" => "Access denied.",
        "error"   => $e->getMessage(),
      ));
    }
  }
  else
  {
    // set response code
    http_response_code(401);
    // tell the user access denied
    echo json_encode(array("message" => "Access denied."));
  }
?>