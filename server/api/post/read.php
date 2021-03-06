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

  // instantiate database and post object

  $database = new Database();
  $db       = $database->getConnection();

  // initialize object
  $post = new Post($db);

  $stmt = $post->read();
  $num  = $stmt->rowCount();

  // check if more than 0 record found
  if ($num > 0)
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
        "id"                   => $id,
        "title"                => $title,
        "body"                 => html_entity_decode($body),
        "createdAt"            => $createdAt,
        "updatedAt"            => $updatedAt,
        "author"               => $author,
        "author_username"      => $author_username,
        "author_email"         => $author_email,
        "author_joinedAt"      => $author_joinedAt,
        "category"             => $category,
        "category_title"       => $category_title,
        "category_description" => html_entity_decode($category_description),
      );

      array_push($posts_arr["records"], $post_item);
    }

    // set response code - 200 OK
    http_response_code(200);

    // show posts data in json format
    echo json_encode($posts_arr);
  }
  else
  {
    // set response code - 200 OK
    http_response_code(200);

    // tell the user no posts found
    echo json_encode(
        array("message" => "No posts found.")
    );
  }
?>