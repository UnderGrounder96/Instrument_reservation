<!DOCTYPE html>
<html>

<?php
require_once("session.php");

# logs out all non-admin
if (!isset($_SESSION["admin"]))
  header("location: logout.php");

$title_page = "Login Admin";
require_once("head.php");
?>

<body>
  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">
    <div class="container">
      <form class="container" action="success.php" method="post">
        <input type="hidden" name="action" value="logAdm">
        <h2 class="text-center mb-2">User type:</h2>
        <div class="d-flex justify-content-center form-check">
          <label class="form-check-label">
            Administrator
            <input class="form-check-input ml-3" type="radio" name="userType" value="admin" checked>
          </label>
        </div>

        <div class="d-flex justify-content-center form-check">
          <label class="form-check-label">
            User
            <input class="form-check-input ml-5" type="radio" name="userType" value="user">
          </label>
        </div>

        <div class="d-flex justify-content-center mt-2 form-group">
          <input type="submit" class="col-md-2 btn btn-outline-primary form-control" value="Log in" />



          </div>
      </form>
    </div>

    <?php
    if (isset($_SESSION["error"])) {
      echo '
        <div class="w3-panel">
          <i class="w3-text-red">
            ' . $_SESSION["error"] . '
          </i>
          </div>
        </div>
      ';

      unset($_SESSION["error"]);
    }
    ?>
  </main>

  <?php
  require_once("footer.php");
  ?>

</body>

</html>