<?php
  class User
  {
    // database connection and table name
    private $conn;
    private $table_name = "users";

    // object properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $joinedAt;

    // constructor
    public function __construct($db){
      $this->conn = $db;
    }

    function create()
    {
      // query to insert record
      $query = "INSERT INTO
                  " . $this->table_name . "
                SET
                  username=:username,
                  email=:email,
                  password=:password";

      // prepare query
      $stmt = $this->conn->prepare($query);

      // sanitize
      $this->username = htmlspecialchars(strip_tags($this->username));
      $this->email    = htmlspecialchars(strip_tags($this->email));
      $this->password = htmlspecialchars(strip_tags($this->password));

      // bind values
      $stmt->bindParam(":username", $this->username);
      $stmt->bindParam(":email", $this->email);

      $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
      $stmt->bindParam(':password', $password_hash);

      // execute query
      if ($stmt->execute())
      {
        return true;
      }
      return false;
    }

    function emailExists()
    {
      // query to check if email exists
      $query = "SELECT id, username, password, joinedAt
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

      // prepare the query
      $stmt = $this->conn->prepare( $query );

      // sanitize
      $this->email=htmlspecialchars(strip_tags($this->email));

      // bind given email value
      $stmt->bindParam(1, $this->email);

      // execute the query
      $stmt->execute();

      // get number of rows
      $num = $stmt->rowCount();

      // if email exists, assign values to object properties for easy access and use for php sessions
      if ($num>0)
      {
        // get record details / values
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // assign values to object properties
        $this->id       = $row['id'];
        $this->username = $row['username'];
        $this->password = $row['password'];
        $this->joinedAt   = $row['joinedAt'];

        // return true because email exists in the database
        return true;
      }
      // return false if email does not exist in the database
      return false;
    }

    public function update()
    {
      // if password needs to be updated
      $password_set=!empty($this->password) ? ", password = :password" : "";

      // if no posted password, do not update the password
      $query = "UPDATE " . $this->table_name . "
                SET
                  username = :username,
                  email = :email
                  {$password_set}
                WHERE id = :id";

      // prepare the query
      $stmt = $this->conn->prepare($query);

      // sanitize
      $this->username = htmlspecialchars(strip_tags($this->username));
      $this->email    = htmlspecialchars(strip_tags($this->email));

      // bind the values from the form
      $stmt->bindParam(':username', $this->username);
      $stmt->bindParam(':email', $this->email);

      // hash the password before saving to database
      if (!empty($this->password))
      {
        $this->password = htmlspecialchars(strip_tags($this->password));
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);
      }

      // unique ID of record to be edited
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
      $this->id = htmlspecialchars(strip_tags($this->id));

      // bind id of record to delete
      $stmt->bindParam(1, $this->id);

      // execute query
      if ($stmt->execute())
      {
        return true;
      }
      return false;
    }
  }
?>