<!DOCTYPE html>
<html>

<?php
session_start();
$title_page = "404 Error";
require_once("head.php");
?>

<body>
  <noscript> Please turn on JavaScript or change browsers!</noscript>

  <?php
  require_once("header.php");
  ?>

  <main class="container text-center" role="main">
    <pre class="lead">
<?php echo $_SERVER['REQUEST_URI']; ?> does not exist, sorry.
</pre>

    <img class="img-fluid mx-auto d-block" loading="lazy" src="../public/img/error.png" loading="lazy" alt="not_found" />
  </main>

  <?php
  require_once("footer.php");
  ?>

</body>

</html>