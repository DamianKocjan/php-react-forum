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
      header('Access-Control-Allow-Headers: X-Requested-With, Authorization');
    }
    exit();
  }

  // include database and object files
  include_once '../config/core.php';
  include_once '../shared/utilities.php';
  include_once '../config/database.php';
  include_once '../objects/post.php';

  // utilities
  $utilities = new Utilities();

  // instantiate database and post object

  $database = new Database();
  $db       = $database->getConnection();

  // initialize object
  $post = new Post($db);

  // query posts
  $stmt = $post->readPaging($from_record_num, $records_per_page);
  $num  = $stmt->rowCount();

  // check if more than 0 record found
  if ($num > 0)
  {
    // posts array
    $posts_arr            = array();
    $posts_arr["records"] = array();
    $posts_arr["paging"]  = array();

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

    // include paging
    $total_rows          = $post->count();
    $page_url            = "{$home_url}post/read_paging.php?";
    $paging              = $utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
    $posts_arr["paging"] = $paging;

    // set response code - 200 OK
    http_response_code(200);

    // make it json format
    echo json_encode($posts_arr);
  }
  else
  {
    // set response code - 404 Not found
    http_response_code(404);

    // tell the user posts does not exist
    echo json_encode(
      array("message" => "No posts found.")
    );
  }
?>