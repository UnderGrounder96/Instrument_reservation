<!DOCTYPE html>
<html>

<?php
session_start();
$title_page = "Home";
require_once("head.php");
?>

<body class="container shadow">
  <noscript> Please turn on JavaScript or change browsers!</noscript>

  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">
    <div class="jumbotron row">
      <div class="col-md">
        <h1 id="title">
          Company&trade;
        </h1>

        <h4 id="title">
          Always delivering goodies.
        </h4>
      </div>

      <?php
      if (!isset($_SESSION["login_user"])) {
      ?>
        <div class="col-md-3">
          <form role="form login-form" action="success.php" method="post">
            <input type="hidden" name="action" value="login">

            <div class="d-flex justify-content-center form-group mt-3">
              <label class="text-center">
                <input type="text" class="form-control md-5" name="username" placeholder="Username" minlength="4" maxlength="25" required autofocus />
              </label>
            </div>

            <div class="d-flex justify-content-center form-group">
              <label class="text-center">
                <input type="password" class="form-control" name="password" placeholder="Password" minlength="4" maxlength="25" autocomplete="new-password" required />
              </label>
            </div>

            <div class="d-flex justify-content-center form-group">
              <label class="text-center">
                <input type="submit" class="btn btn-outline-success form-control" value="Log in" />
              </label>
            </div>
          </form>

        <?php
        if (isset($_SESSION["error"])) {
          echo '
          <div class="alert alert-danger" role="alert">
            <em>'
            . $_SESSION["error"] . '
            </em>
          </div>
        ';

          unset($_SESSION["error"]);
        }

        echo '</div>';
      }
        ?>

        </div>
  </main>

  <?php
  require_once("footer.php");
  ?>

  <div class="spinner" role="status">
    <span class="sr-only">Loading...</span>
  </div>

</body>

</html>