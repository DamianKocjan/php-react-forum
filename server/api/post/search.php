<?php
  // required headers
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");

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

  // include database and object files
  include_once '../config/core.php';
  include_once '../config/database.php';
  include_once '../objects/post.php';

  // instantiate database and post object

  $database = new Database();
  $db       = $database->getConnection();

  // initialize object
  $post = new Post($db);

  // get keywords
  $keywords=isset($_GET["s"]) ? $_GET["s"] : "";

  // query posts
  $stmt = $post->search($keywords);
  $num  = $stmt->rowCount();

  // check if more than 0 record found
  if($num > 0)
  {
    // posts array
    $posts_arr            = array();
    $posts_arr["records"] = array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      // extract row
      // this will make $row['name'] to
      // just $name only
      extract($row);

      $post_item = array(
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

      array_push($posts_arr["records"], $post_item);
    }

    // set response code - 200 OK
    http_response_code(200);

    // show posts data
    echo json_encode($posts_arr);
  }
  else
  {
    // set response code - 404 Not found
    http_response_code(404);

    // tell the user no posts found
    echo json_encode(
      array("message" => "No posts found.")
    );
  }
?>