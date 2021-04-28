<?php
  // required headers
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST, OPTIONS");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, authorization");

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

  // get database connection
  include_once 'config/database.php';

  // instantiate user object
  include_once 'objects/user.php';

  $database = new Database();
  $db       = $database->getConnection();

  $user = new User($db);

  // get posted data
  $data = json_decode(file_get_contents("php://input"));

  // set product property values
  $user->email  = $data->email;
  $email_exists = $user->emailExists();

  // generate json web token
  include_once 'config/core.php';
  include_once 'libs/php-jwt-master/src/BeforeValidException.php';
  include_once 'libs/php-jwt-master/src/ExpiredException.php';
  include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
  include_once 'libs/php-jwt-master/src/JWT.php';
  use \Firebase\JWT\JWT;

  // check if email exists and if password is correct
  if ($email_exists && password_verify($data->password, $user->password))
  {
    $access_token = array(
      "iat"  => $issued_at,
      "exp"  => $access_expiration_time,
      "iss"  => $issuer,
      "data" => array(
        "id"       => $user->id,
        "username" => $user->username,
        "email"    => $user->email,
        "joinedAt" => $user->joinedAt,
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
        "joinedAt" => $user->joinedAt,
      )
    );

    // set response code
    http_response_code(200);

    // generate jwt
    $jwt_access  = JWT::encode($access_token, $key);
    $jwt_refresh = JWT::encode($refresh_token, $key);
    echo json_encode(
          array(
            "message"      => "Successful login.",
            "accessToken"  => $jwt_access,
            "refreshToken" => $jwt_refresh,
          )
         );
  }
  // login failed
  else
  {
    // set response code
    http_response_code(401);

    // tell the user login failed
    echo json_encode(array("message" => "Login failed."));
  }
?>