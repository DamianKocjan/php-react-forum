<?php
  // required headers
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: GET, OPTIONS");
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

  // include database and object files
  include_once '../config/database.php';
  include_once '../objects/post.php';

  // get database connection

  $database = new Database();
  $db       = $database->getConnection();

  // prepare post object
  $post = new Post($db);

  // set ID property of record to read
  $post->id = isset($_GET['id']) ? $_GET['id'] : die();

  // read the details of post to be edited
  $post->readOne();

  if ($post->title != null)
  {
    // create array
    $post_arr = array(
      "id"                   => $post->id,
      "title"                => $post->title,
      "body"                 => $post->body,
      "createdAt"            => $post->createdAt,
      "updatedAt"            => $post->updatedAt,
      "author"               => $post->author,
      "author_username"      => $post->author_username,
      "author_email"         => $post->author_email,
      "author_joinedAt"      => $post->author_joinedAt,
      "category"             => $post->category,
      "category_title"       => $post->category_title,
      "category_description" => $post->category_description,
    );

    // set response code - 200 OK
    http_response_code(200);

    // make it json format
    echo json_encode($post_arr);
  }
  else
  {
    // set response code - 404 Not found
    http_response_code(404);

    // tell the user post does not exist
    echo json_encode(array("message" => "Post does not exist."));
  }
?>