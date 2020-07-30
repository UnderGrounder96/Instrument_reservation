<!DOCTYPE html>
<html>

<?php
require_once("session.php");
require_once("functions.php");

// logs out all non-admin
if (!isset($_SESSION["admin"])) {
  header("location: logout.php");
  exit;
}

$title_page = "Admin";
require_once("head.php");

static $content = "";
?>

<body class="container shadow">
  <noscript> Please turn on JavaScript or change browsers!</noscript>

  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">
    <div class="container">
      <ul class="nav nav-tabs list-inline mx-auto justify-content-center" role="tablist">
        <li id="inst" class="nav-link list-inline-item">Add/Change instrument</li>
        <li id="rig" class="nav-link list-inline-item">Add/Change rights</li>
        <li id="user" class="nav-link list-inline-item">Add/Change user</li>
      </ul>

      <div class="hide row container" id="inst1">
        <form class="col container" action="success.php" method="post">
        <input type="hidden" name="action" value="addInst">

          <?php
          try {

            if (isset($_GET["inst"])) {
              unset($_SESSION["rig"]);
              unset($_SESSION["user"]);

              $_SESSION["inst"] = testInput($_GET["inst"]);

              $sql = "SELECT * FROM instruments WHERE id_instrument='{$_SESSION['inst']}' ORDER BY id_instrument;";
              $result = $db->query($sql);

              if ($db->affected_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $content = "
                    <div class=\"form-group mt-3\">
                    <label>Model:
                    <input type=\"text\" class=\"form-control validate\" maxlength=\"30\" name=\"model\" autofocus value=\"{$row['model']}\" required>
                    </label>
                    </div>

                    <div class=\"form-group\">
                    <label>Is the device working?
                    <select class=\"form-control mt-2 col-xs-1 validate\" name=\"active\" style=\"width:80px\" required>
                    <option value=\"1\">yes</option>
                    <option value=\"0\">no</option>
                    </select>
                    </label>
                    </div>

                    <div class=\"form-group\">
                      <input type=\"Submit\" class=\"btn btn-outline-primary btn-block btn-sm\" value=\"Edit\" style=\"width:90px\" />
                    </div>";
                }
              } else {
                throw new Exception("Could not find record <br />" . $db->error);
              }
            } else {
              $content = "
                <div class=\"form-group mt-3\">
                <label>Model:
                <input type=\"text\" class=\"form-control validate\" maxlength=\"30\" name=\"model\" autofocus autocomplete=\"off\" required>
                </label>
                </div>

                <div class=\"form-group\">
                <label>Is the device working?
                <select class=\"form-control mt-2 col-xs-1 validate\" name=\"active\" style=\"width:80px\" required>
                <option value=\"1\">yes</option>
                <option value=\"0\">no</option>
                </select>
                </label>
                </div>

                <div class=\"form-group\">
                <input type=\"Submit\" class=\"btn btn-outline-success btn-block btn-sm\" value=\"Add\" style=\"width:90px\" />
                </div>";
            }

            echo $content;
          } catch (Exception $e) {
            $_SESSION["err1"] = $e->getMessage();
          }

          if (isset($_SESSION["err1"])) {
            echo '
            <div class="alert alert-danger mt-3" role="alert">
              <em>'
              . $_SESSION["err1"] .
              '</em>
            </div>';

            unset($_SESSION["err1"]);
          } else if (empty($_GET["inst"])) {
            echo '
            <div class="alert alert-secondary w-50 mt-3" role="alert">
              <em>
              Add or click to edit instrument.
              </em>
            </div>';
          }

          ?>
        </form>

        <div class="col-sm-3 mt-4 container table-responsive-sm">
          <?php
          $sql = "SELECT * FROM instruments ORDER BY id_instrument;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
          ?>
            <table class="table table-condensed table-hover">
              <tr>
                <th>Id</th>
                <th>Model</th>
                <th>Active</th>
              </tr>
            <?php
            while ($row = $result->fetch_assoc())
              echo "
              <tr>
              <td>
                <a href='admin.php?inst={$row['id_instrument']}'>{$row['id_instrument']}</a>
              </td>

              <td>
                <a href='admin.php?inst={$row['id_instrument']}'>{$row['model']}</a>
              </td>

              <td>
                <a href='admin.php?inst={$row['id_instrument']}'>"._check($row['active'])."</a>
              </td>
              </tr>";
          }
            ?>
            </table>
        </div>
      </div>

      <div class="row container hide" id="rig1">
        <form class="col container" action="success.php" method="post">
          <input type="hidden" name="action" value="addRig">

          <?php
          try {

            if (isset($_GET["rig"])) {
              unset($_SESSION["inst"]);
              unset($_SESSION["user"]);

              $_SESSION["rig"] = testInput($_GET["rig"]);

              $sql = "SELECT rights.id_right, instruments.id_instrument, instruments.model, users.username, users.id_user FROM ((rights INNER JOIN users ON rights.id_user=users.id_user) INNER JOIN instruments ON rights.id_instrument=instruments.id_instrument) WHERE rights.id_right='{$_SESSION['rig']}' ORDER BY rights.id_right;";
              $result = $db->query($sql);

              if ($db->affected_rows > 0) {
                $row = $result->fetch_assoc();

                $content = "<div class=\"form-group\">
              <label>Instrument & User:
              <select class=\"form-control\" name=\"rig\" readonly required>
              <option value=\"{$row['id_right']}\">'{$row['model']}' '{$row['username']}'</option>
              </select>
              </label>
              </div>

              <input type=\"hidden\" name=\"inst\" value=\"{$row['id_instrument']}\">
              <input type=\"hidden\" name=\"user\" value=\"{$row['id_user']}\">

              <div class=\"form-group\">
              <label>Rights:
              <select class=\"form-control mt-2 col-xs-1 validate\" name=\"pow\" style=\"width:100px\" autofocus required>
              <option value=\"2\">admin</option>
              <option value=\"1\" selected>user</option>
              <option value=\"0\">no access</option>
              </select>
              </label>
              </div>

              <div class=\"form-group\">
                <input type=\"Submit\" class=\"btn btn-outline-primary btn-block btn-sm\" value=\"Edit\" style=\"width:90px\" />
              </div>";
              }
            } else {
              $sql = "SELECT id_instrument, model FROM instruments WHERE active>0;";
              $result = $db->query($sql);
              $content = "
              <div class=\"form-group\">
              <label>Instrument:<br>
                <select class=\"form-control\" name=\"inst\" required>";

              if ($db->affected_rows > 0)
                while ($row = $result->fetch_assoc())
                  $content .= "<option value=\"{$row['id_instrument']}\">{$row['model']}</option>";

              $content .= "
                </select>
              </label>
              </div>

            <div class=\"form-group\">
              <label> User:<br>
                <select class=\"form-control\" name=\"user\" required>
                  ";
              $sql = "SELECT id_user, username FROM users WHERE active>0;";
              $result = $db->query($sql);
              if ($db->affected_rows > 0)
                while ($row = $result->fetch_assoc())
                  $content .= "<option value=\"{$row['id_user']}\">{$row['username']}</option>";

              $content .= "
              </select>
            </label>
            </div>

            <div class=\"form-group\">
            <label>Rights:
            <select class=\"form-control mt-2 col-xs-1 validate\" name=\"pow\" style=\"width:100px\" autofocus required>
            <option value=\"2\">admin</option>
            <option value=\"1\" selected>user</option>
            <option value=\"0\">no access</option>
            </select>
            </label>
            </div>

            <div class=\"form-group mt-2\">
              <input type=\"Submit\" class=\"btn btn-outline-success btn-block btn-sm\" value=\"Add\" style=\"width:90px\" />
            </div>";
            }

            echo $content;
          } catch (Exception $e) {
            $_SESSION["err2"] = $e->getMessage();
          }

          if (isset($_SESSION["err2"])) {
            echo '
            <div class="alert alert-danger mt-3" role="alert">
              <em>'
              . $_SESSION["err2"] .
              '</em>
            </div>';

            unset($_SESSION["err2"]);
          } else if (empty($_GET["rig"])) {
            echo '
            <div class="alert alert-secondary w-50 mt-3" role="alert">
              <em>
              Add or click to edit users\' rights.
              </em>
            </div>';
          }
          ?>

        </form>

        <div class="col-sm-5 mt-4 container table-responsive-sm">
          <?php
          $sql = "SELECT rights.id_right, instruments.model, users.username, rights.power FROM ((rights INNER JOIN users ON rights.id_user=users.id_user) INNER JOIN instruments ON rights.id_instrument=instruments.id_instrument) ORDER BY  rights.id_right;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
          ?>
            <table class="table table-condensed table-hover">
              <tr>
                <th>Id</th>
                <th>User</th>
                <th>Model</th>
                <th>Power</th>
              </tr>

            <?php
            while ($row = $result->fetch_assoc())
              echo "
              <tr>
              <td>
              <a href=\"admin.php?rig={$row['id_right']}\">{$row['id_right']}</a>
              </td>

              <td>
              <a href=\"admin.php?rig={$row['id_right']}\">{$row['username']}</a>
              </td>

              <td>
              <a href=\"admin.php?rig={$row['id_right']}\">{$row['model']}</a>
              </td>

              <td>
              <a href=\"admin.php?rig={$row['id_right']}\">"._power($row['power'])."</a>
              </td>
              </tr>";
          }
            ?>
            </table>
        </div>
      </div>

      <div class="row container hide" id="user1">
        <form class="col container" action="success.php" method="post" autocomplete="off">
          <?php
          try {

            if (isset($_GET["use"])) {
              unset($_SESSION["rig"]);
              unset($_SESSION["inst"]);

              $_SESSION["user"] = testInput($_GET["use"]);

              $sql = "SELECT * FROM users WHERE id_user='{$_SESSION['user']}' ORDER BY id_user;";
              $result = $db->query($sql);

              if ($db->affected_rows > 0) {
                $row = $result->fetch_assoc();

                $content = "
                <input type=\"hidden\" name=\"action\" value=\"editUser\">

                <input type=\"hidden\" name=\"id_user\" value=\"{$row['id_user']}\">

                <div class=\"form-group\">
                  <label>First name:
                  <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"fname\" value=\"{$row['first_name']}\" placeholder=\"First Name\" required>
                  </label>
                </div>

                <div class=\"form-group\">
                  <label>Last name:
                  <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"lname\" value=\"{$row['last_name']}\" placeholder=\"Last Name\" required>
                  </label>
                </div>

                <div class=\"form-group\">
                  <label>Login:
                  <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"username\" value=\"{$row['username']}\" placeholder=\"username\" required>
                  </label>
                </div>

                <div class=\"form-group\">
                  <label>E-mail:
                  <input type=\"email\" class=\"form-control\" name=\"email\" maxlength=\"30\" value=\"{$row['email']}\" placeholder=\"your@email.com\" required>
                  </label>
                </div>

                <div class=\"form-group\">
                <label>Will the user be active?
                <select class=\"form-control mt-2 col-xs-1 validate\" name=\"active\" style=\"width:80px\" required>
                <option value=\"1\">yes</option>
                <option value=\"0\">no</option>
                </select>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Will the user be an admininstrator?
                <select class=\"form-control mt-2 col-xs-1 validate\" name=\"admin\" style=\"width:80px\" autofocus required>
                <option value=\"1\">yes</option>
                <option value=\"0\" selected>no</option>
                </select>
                </label>
              </div>

                <div class=\"form-group\">
                  <input type=\"Submit\" class=\"btn btn-outline-primary btn-block btn-sm\" value=\"Edit\" style=\"width:90px\" />
                </div>";
              } else
                throw new Exception("Error updating record1: " . $db->error);
            } else {
              $content = "
              <input type=\"hidden\" name=\"action\" value=\"addUser\">

              <div class=\"form-group\">
                <label>First name:
                <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"fname\" placeholder=\"First Name\" required>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Last name:
                <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"lname\" placeholder=\"Last Name\" required>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Login:
                <input type=\"text\" class=\"form-control validate\" maxlength=\"25\" name=\"username\" placeholder=\"username\" required>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Password:
                <input type=\"password\" class=\"form-control validate\" maxlength=\"25\" name=\"password\" autocomplete=\"new-password\" placeholder=\"password\" required>
                </label>
              </div>

              <div class=\"form-group\">
                <label>E-mail:
                <input type=\"email\" class=\"form-control\" name=\"email\" maxlength=\"30\" placeholder=\"your@email.com\" required>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Will the user be active?
                <select class=\"form-control mt-2 col-xs-1 validate\" name=\"active\" style=\"width:80px\" required>
                <option value=\"1\">yes</option>
                <option value=\"0\">no</option>
                </select>
                </label>
              </div>

              <div class=\"form-group\">
                <label>Will the user be an admininstrator?
                <select class=\"form-control mt-2 col-xs-1 validate\" name=\"admin\" style=\"width:80px\" autofocus required>
                <option value=\"1\">yes</option>
                <option value=\"0\" selected>no</option>
                </select>
                </label>
              </div>

              <div class=\"form-group\">
              <input type=\"Submit\" class=\"btn btn-outline-success btn-block btn-sm\" value=\"Add\" style=\"width:90px\" />
            </div>";
            }

            echo $content;
          } catch (Exception $e) {
            $_SESSION["err3"] = $e->getMessage();
          }

          if (isset($_SESSION["err3"])) {
            echo '
            <div class="alert alert-danger mt-3" role="alert">
              <em>'
              . $_SESSION["err3"] .
              '</em>
            </div>';
            unset($_SESSION["err3"]);
          } else if (empty($_GET["use"])) {
            echo '
            <div class="alert alert-secondary w-50 mt-3" role="alert">
              <em>
              Add or click to edit user.
              </em>
            </div>
          ';
          }
          ?>
        </form>

        <div class="col-md mt-4 container table-responsive-md">
          <?php
          $sql = "SELECT id_user, first_name, last_name, username, email, active, admin FROM users ORDER BY id_user;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
          ?>
            <table class="table table-condensed table-hover">
              <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Login</th>
                <th>e-mail</th>
                <th>Admin</th>
                <th>Active</th>
              </tr>

            <?php
            while ($row = $result->fetch_assoc())
              echo "
              <tr>
              <td>
              <a href=\"admin.php?use={$row['id_user']}\">{$row['id_user']}</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">{$row['first_name']}</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">{$row['last_name']}</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">{$row['username']}</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">{$row['email']}</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">"._check($row['admin'])."</a>
              </td>

              <td>
              <a href=\"admin.php?use={$row['id_user']}\">"._check($row['active'])."</a>
              </td>
              </tr>";
          }
            ?>
            </table>
        </div>
      </div>

    </div>
  </main>

  <?php
  require_once("footer.php");
  ?>

  <script>
    jQuery(() => {
      <?php
      if (isset($_GET["use"])) {
      ?>
        jQuery("#user").addClass("active");
        jQuery("#user1").show();
      <?php
      } else if (isset($_GET["rig"])) {
      ?>
        jQuery("#rig").addClass("active");
        jQuery("#rig1").show();
      <?php
      } else {
      ?>
        jQuery("#inst").addClass("active");
        jQuery("#inst1").show();
      <?php
      }
      ?>
    });
  </script>
</body>

</html>