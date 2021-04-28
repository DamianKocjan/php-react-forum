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

  // include database and object files
  include_once '../config/database.php';
  include_once '../objects/post.php';

  // get database connection

  $database = new Database();
  $db       = $database->getConnection();

  // prepare post object
  $post = new Post($db);

  // get id of post to be edited
  $data = json_decode(file_get_contents("php://input"));

  // set ID property of post to be edited
  $post->id = $data->id;

  // check existence of post
  if ($post->read_one())
  {
    // get token
    $headers = apache_request_headers();
    $token   = $headers['authorization'];

    // if token is not empty
    if ($token)
    {
      // if decode succeed, show user details
      try
      {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
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
        exit();
      }

      if ($decoded->data->id != $post->author)
      {
        // set response code
        http_response_code(401);

        // tell the user access denied  & show error message
        echo json_encode(array(
          "message" => "Access denied.",
          "error"   => $e->getMessage(),
        ));
        exit();
      }
    }
  }
  else
  {
    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to update post."));
    exit();
  }

  // set post property values
  $post->title     = $data->title;
  $post->body      = $data->body;
  $post->createdAt = $data->createdAt;
  $post->updatedAt = $data->updatedAt;
  $post->category  = $data->category;

  // update the post
  if ($post->update())
  {
    // set response code - 200 ok
    http_response_code(200);

    // tell the user
    echo json_encode(array("message" => "Post was updated."));
  }
  // if unable to update the post, tell the user
  else
  {
    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to update post."));
  }
?>