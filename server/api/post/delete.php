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

  // include database and object file
  include_once '../config/database.php';
  include_once '../objects/post.php';

  // get database connection
  $database = new Database();
  $db       = $database->getConnection();

  // prepare post object
  $post = new Post($db);

  // get post id
  $data = json_decode(file_get_contents("php://input"));

  // set post id to be deleted
  $post->id = $data->id;

  // check existence of post
  if (!$post->readOne())
  // {
    // // get token
    // $headers = apache_request_headers();
    // $token   = $headers['authorization'];

    // // if token is not empty
    // if ($token)
    // {
    //   // if decode succeed, show user details
    //   try
    //   {
    //     // decode jwt
    //     $decoded = JWT::decode($token, $key, array('HS256'));
    //   }
    //   // if decode fails, it means jwt is invalid
    //   catch (Exception $e)
    //   {
    //     // set response code
    //     http_response_code(401);

    //     // tell the user access denied & show error message
    //     echo json_encode(array(
    //       "message" => "Access denied.",
    //       "error"   => $e->getMessage(),
    //     ));
    //     exit();
    //   }

    //   // check if user is owner of this post
    //   if ($decoded->data->id != $post->author)
    //   {
    //     // set response code
    //     http_response_code(401);

    //     // tell the user access denied
    //     echo json_encode(array(
    //       "message" => "Access denied.",
    //     ));
    //     exit();
    //   }
    // }
  // }
  // else
  {
    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to delete post."));
    exit();
  }

  // delete the post
  if ($post->delete())
  {
    // set response code - 200 ok
    http_response_code(200);

    // tell the user
    echo json_encode(array("message" => "Post was deleted."));
  }

  // if unable to delete the post
  else
  {
    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to delete post."));
  }
?>