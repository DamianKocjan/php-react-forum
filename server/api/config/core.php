<?php
  // show error reporting
  ini_set('display_errors', 1);
  error_reporting(E_ALL);

  // home page url
  $home_url = "http://localhost/api/";

  // page given in URL parameter, default page is one
  $page = isset($_GET['page']) ? $_GET['page'] : 1;

  // set number of records per page
  $records_per_page = 5;

  // calculate for the query LIMIT clause
  $from_record_num = ($records_per_page * $page) - $records_per_page;

  // set your default time-zone
  date_default_timezone_set('Europe/Warsaw');

  // variables used for jwt
  $key                     = "example_key";
  $issued_at               = time();
  $access_expiration_time  = $issued_at + (60 * 60 * 24 * 7); // valid for 7 days
  $refresh_expiration_time = $issued_at + (60 * 60 * 24 * 7 * 2); // valid for 14 days
  $issuer                  = "http://localhost/api/";
?>