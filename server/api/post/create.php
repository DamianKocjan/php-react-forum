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

  // required to decode jwt
  include_once '../config/core.php';
  include_once '../libs/php-jwt-master/src/BeforeValidException.php';
  include_once '../libs/php-jwt-master/src/ExpiredException.php';
  include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
  include_once '../libs/php-jwt-master/src/JWT.php';
  use \Firebase\JWT\JWT;

  // get database connection
  include_once '../config/database.php';

  // instantiate post object
  include_once '../objects/post.php';

  // // get token
  // $headers = apache_request_headers();
  // $token   = $headers['authorization'][0];

  // // if token is not empty
  // if ($token)
  // {
  //   // if decode succeed, show user details
  //   try
  //   {
  //     // decode jwt
  //     $decoded = JWT::decode($jwt, $key, array('HS256'));
  //   }
  //   // if decode fails, it means jwt is invalid
  //   catch (Exception $e)
  //   {
  //     // set response code
  //     http_response_code(401);

  //     // tell the user access denied  & show error message
  //     echo json_encode(array(
  //       "message" => "Access denied.",
  //       "error"   => $e->getMessage(),
  //     ));
  //     exit();
  //   }
  // }
  // else
  // {
  //   // set response code
  //   http_response_code(401);

  //   // tell the user access denied  & show error message
  //   echo json_encode(array(
  //     "message" => "Access denied.",
  //   ));
  //   exit();
  // }

  $database = new Database();
  $db       = $database->getConnection();

  $post = new Post($db);

  // get posted data
  $data = json_decode(file_get_contents("php://input"));

  // make sure data is not empty
  if (
    !empty($data->title) &&
    !empty($data->body) &&
    !empty($data->author) &&
    !empty($data->category)
  )
  {
    // set post property values
    $post->title    = $data->title;
    $post->body     = $data->body;
    // $post->author   = $decoded->data->id;
    $post->author   = $data->author;
    $post->category = $data->category;

    // create the post
    if ($post->create())
    {
      // set response code - 201 created
      http_response_code(201);

      // tell the user
      echo json_encode(array("message" => "Post was created."));
    }

    // if unable to create the post, tell the user
    else
    {
      // set response code - 503 service unavailable
      http_response_code(503);

      // tell the user
      echo json_encode(array("message" => "Unable to create post."));
    }
  }
  // tell the user data is incomplete
  else
  {
    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Unable to create post. Data is incomplete."));
  }
?>