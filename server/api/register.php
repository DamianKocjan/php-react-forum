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

  // make sure data is not empty
  if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->password2)
  )
  {
    // set user property values
    $user->username  = $data->username;
    $user->email     = $data->email;

    if ($data->password == $data->password2)
    {
      $user->password = $data->password;
    }
    else
    {
      http_response_code(400);
      echo json_encode(array("message" => "Unable to register user. Passwords does not match."));

      exit();
    }

    // create the user
    if ($user->create())
    {
      // set response code - 201 created
      http_response_code(201);

      // tell the user
      echo json_encode(array("message" => "User was registered."));
    }
    // if unable to create the user, tell the user
    else
    {
      // set response code - 503 service unavailable
      http_response_code(503);

      // tell the user
      echo json_encode(array("message" => "Unable to register user."));
    }
  }
  // tell the user data is incomplete
  else
  {
    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Unable to register user. Data is incomplete."));
  }
?>