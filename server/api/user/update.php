<?php
  // required headers
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, authorization, X-Requested-With");

  // respond to preflights
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
  {
    // return only the headers and not the content
    // only allow CORS if we're doing a GET - i.e. no saving for now.
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET')
    {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Headers: X-Requested-With, authorization');
    }
    exit();
  }

  // required to encode json web token
  include_once 'config/core.php';
  include_once 'libs/php-jwt-master/src/BeforeValidException.php';
  include_once 'libs/php-jwt-master/src/ExpiredException.php';
  include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
  include_once 'libs/php-jwt-master/src/JWT.php';
  use \Firebase\JWT\JWT;

  // files needed to connect to database
  include_once 'config/database.php';
  include_once 'objects/user.php';

  // get database connection

  $database = new Database();
  $db       = $database->getConnection();

  // instantiate user object
  $user = new User($db);

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

      // set user property values
      $user->id        = $decoded->data->id;
      $user->username  = $data->firstname;
      $user->email     = $data->email;
      $user->password  = $data->password;
      $user->joined    = $data->joined;

      // update the user record
      if ($user->update())
      {
        // we need to re-generate jwt because user details might be different
        $access_token = array(
          "iat"  => $issued_at,
          "exp"  => $access_expiration_time,
          "iss"  => $issuer,
          "data" => array(
            "id"       => $user->id,
            "username" => $user->username,
            "email"    => $user->email,
            "joined"   => $user->joined,
          )
        );

        $refresh_token = array(
          "iat"  => $issued_at,
          "exp"  => $refresh_expiration_time,
          "iss"  => $issuer,
          "data" => array(
            "id"       => $user->id,
            "username" => $user->username,
            "email"    => $user->email,
            "joined"   => $user->joined,
          )
        );

        $jwt_access  = JWT::encode($access_token, $key);
        $jwt_refresh = JWT::encode($refresh_token, $key);

        // set response code
        http_response_code(200);

        // response in json format
        echo json_encode(
              array(
                "message"      => "User was updated.",
                "accessToken"  => $jwt_access,
                "refreshToken" => $jwt_refresh,
              )
             );
      }
      // message if unable to update user
      else
      {
        // set response code
        http_response_code(401);

        // show error message
        echo json_encode(array("message" => "Unable to update user."));
      }
    }
    // if decode fails, it means jwt is invalid
    catch (Exception $e)
    {
      // set response code
      http_response_code(401);

      // show error message
      echo json_encode(array(
          "message" => "Access denied.",
          "error"   => $e->getMessage()
      ));
    }
  }
  // show error message if jwt is empty
  else
  {
    // set response code
    http_response_code(401);

    // tell the user access denied
    echo json_encode(array("message" => "Access denied."));
  }
?>