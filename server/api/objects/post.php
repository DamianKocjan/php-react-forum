<?php
  class Post
  {
    // database connection and table name
    private $conn;
    private $table_name = "posts";

    // object properties
    public $id;
    public $title;
    public $body;
    public $createdAt;
    public $updatedAt;

    public $author;
    public $author_username;
    public $author_email;
    public $author_joinedAt;

    public $category;
    public $category_title;
    public $category_description;

    // constructor with $db as database connection
    public function __construct($db)
    {
      $this->conn = $db;
    }

    function read()
    {
      // select all query
      $query = "SELECT
                  c.title as category_title,
                  c.description as category_description,
                  a.username as author_username,
                  a.email as author_email,
                  a.joinedAt as author_joinedAt,
                  p.id,
                  p.title,
                  p.body,
                  p.createdAt,
                  p.updatedAt,
                  p.author,
                  p.category
                FROM
                  " . $this->table_name . " p
                LEFT JOIN
                  categories c
                  ON p.category = c.id
                LEFT JOIN
                  users a
                  ON p.author = a.id
                ORDER BY
                  p.createdAt DESC";

      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // execute query
      $stmt->execute();

      return $stmt;
    }

    function readOne()
    {
      // query to read single record
      $query = "SELECT
                  c.title as category_title,
                  c.description as category_description,
                  a.username as author_username,
                  a.email as author_email,
                  a.joinedAt as author_joinedAt,
                  p.id,
                  p.title,
                  p.body,
                  p.createdAt,
                  p.updatedAt,
                  p.author,
                  p.category
                FROM
                  " . $this->table_name . " p
                LEFT JOIN
                  categories c
                  ON p.category = c.id
                LEFT JOIN
                  users a
                  ON p.author = a.id
                WHERE
                  p.id = ?
                LIMIT
                  0,1";

      // prepare query statement
      $stmt = $this->conn->prepare( $query );

      // bind id of product to be updated
      $stmt->bindParam(1, $this->id);

      // execute query
      if ($stmt->execute())
      {
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        $this->title                = $row['title'];
        $this->body                 = $row['body'];
        $this->createdAt            = $row['createdAt'];
        $this->updatedAt            = $row['updatedAt'];

        $this->author               = $row['author'];
        $this->author_username      = $row['author_username'];
        $this->author_email         = $row['author_email'];
        $this->author_joinedAt      = $row['author_joinedAt'];

        $this->category             = $row['category'];
        $this->category_title       = $row['category_title'];
        $this->category_description = $row['category_description'];

        return true;
      }
      return false;
    }

    function create()
    {
      // query to insert record
      $query = "INSERT INTO
                  " . $this->table_name . "
                SET
                  title=:title,
                  body=:body,
                  author=:author,
                  category=:category";

      // prepare query
      $stmt = $this->conn->prepare($query);

      // sanitize
      $this->title    = htmlspecialchars(strip_tags($this->title));
      $this->body     = htmlspecialchars(strip_tags($this->body));
      $this->author   = htmlspecialchars(strip_tags($this->author));
      $this->category = htmlspecialchars(strip_tags($this->category));

      // bind values
      $stmt->bindParam(":title", $this->title);
      $stmt->bindParam(":body", $this->body);
      $stmt->bindParam(":author", $this->author);
      $stmt->bindParam(":category", $this->category);

      // execute query
      if ($stmt->execute())
      {
        return true;
      }
      return false;
    }

    function update()
    {
      // update query
      $query = "UPDATE
                  " . $this->table_name . "
                SET
                  title=:title,
                  body=:body,
                  author=:author,
                  category=:category
                WHERE
                  id = :id";

      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // sanitize
      $this->title    = htmlspecialchars(strip_tags($this->title));
      $this->body     = htmlspecialchars(strip_tags($this->body));
      $this->author   = htmlspecialchars(strip_tags($this->author));
      $this->category = htmlspecialchars(strip_tags($this->category));
      $this->id       = htmlspecialchars(strip_tags($this->id));

      // bind new values
      $stmt->bindParam(":title", $this->title);
      $stmt->bindParam(":body", $this->body);
      $stmt->bindParam(":author", $this->author);
      $stmt->bindParam(":category", $this->category);
      $stmt->bindParam(':id', $this->id);

      // execute the query
      if ($stmt->execute())
      {
        return true;
      }
      return false;
    }

    function delete()
    {
      // delete query
      $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

      // prepare query
      $stmt = $this->conn->prepare($query);

      // sanitize
      $this->id=htmlspecialchars(strip_tags($this->id));

      // bind id of record to delete
      $stmt->bindParam(1, $this->id);

      // execute query
      if ($stmt->execute())
      {
        return true;
      }
      return false;
    }

    function search($keywords)
    {
      // select all query
      $query = "SELECT
                  c.title as category_title,
                  c.description as category_description,
                  a.username as author_username,
                  a.email as author_email,
                  a.joinedAt as author_joinedAt,
                  p.id,
                  p.title,
                  p.body,
                  p.createdAt,
                  p.updatedAt,
                  p.author,
                  p.category
                FROM
                  " . $this->table_name . " p
                LEFT JOIN
                  categories c
                  ON p.category = c.id
                LEFT JOIN
                  users a
                  ON p.author = a.id
                WHERE
                  p.title LIKE ? OR
                  p.body LIKE ? OR
                  c.title LIKE ? OR
                  c.description LIKE ? OR
                  a.username LIKE ?
                ORDER BY
                  p.createdAt DESC";

      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // sanitize
      $keywords = htmlspecialchars(strip_tags($keywords));
      $keywords = "%{$keywords}%";

      // bind
      $stmt->bindParam(1, $keywords);
      $stmt->bindParam(2, $keywords);
      $stmt->bindParam(3, $keywords);
      $stmt->bindParam(4, $keywords);
      $stmt->bindParam(5, $keywords);

      // execute query
      $stmt->execute();

      return $stmt;
    }

    public function readPaging($from_record_num, $records_per_page)
    {
      // select query
      $query = "SELECT
                  c.title as category_title,
                  c.description as category_description,
                  a.username as author_username,
                  a.email as author_email,
                  a.joinedAt as author_joinedAt,
                  p.id,
                  p.title,
                  p.body,
                  p.createdAt,
                  p.updatedAt,
                  p.author,
                  p.category
                FROM
                  " . $this->table_name . " p
                LEFT JOIN
                  categories c
                  ON p.category = c.id
                LEFT JOIN
                  users a
                  ON p.author = a.id
                ORDER BY p.createdAt DESC
                LIMIT ?, ?";

      // prepare query statement
      $stmt = $this->conn->prepare( $query );

      // bind variable values
      $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
      $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

      // execute query
      $stmt->execute();

      // return values from database
      return $stmt;
    }

    public function count()
    {
      $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";

      $stmt = $this->conn->prepare( $query );
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      return $row['total_rows'];
    }
  }
?>