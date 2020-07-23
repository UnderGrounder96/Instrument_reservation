<!DOCTYPE html>
<html>

<?php
require_once("session.php");

// logs out all non-admin
if (!isset($_SESSION["admin"])){
  header("location: logout.php");
  exit;
}

$title_page = "Admin";
require_once("head.php");

static $err1 = "", $err2 = "", $err3 = "", $content;
?>

<body>
<noscript> Please turn on JavaScript or change browsers!</noscript>

  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">

  <div id="w3-top">
    <header class="w3-container">
      <div class="w3-container w3-right">
        <h1> Welcome <?php echo $_SESSION["login_user"]; ?>,
          <a href="logout.php">sign out</a>
        </h1>
      </div>
    </header>
  </div>

  <div class="w3-container">
    <ul class="w3-ul w3-hoverable w3-text-shadow w3-half w3-container" style="max-width:285px">
      <li class="w3-padding-16" id="inst">Add/Change instrument</li>
      <li class="w3-padding-16" id="rig">Add/Change rights</li>
      <li class="w3-padding-16" id="user">Add/Change user</li>
    </ul>

    <fieldset class="w3-half w3-container " style="width:1300px">
      <legend>Please choose and fill the form:</legend>

      <div class="w3-container hide" id="inst1">
        <?php
        try {
          echo "<form class=\"w3-container w3-half\" action=\"success.php\" method=\"post\">";

          if (isset($_GET["inst"])) {
            unset($_SESSION["rig"]);
            unset($_SESSION["user"]);

            $_SESSION["inst"] = $_GET["inst"];

            $sql = "SELECT * FROM instruments WHERE idInstrument='{$_SESSION['inst']}' ORDER BY idInstrument;";
            $result = $db->query($sql);

            if ($db->affected_rows > 0)
              while ($row = $result->fetch_assoc()) {
                $content = "<input type=\"hidden\" name=\"action\" value=\"editInst\">Model:<br>
											<input type=\"text\" maxlength=\"30\" size=\"18\" name=\"model\" autofocus value=\"";
                if (isset($_SESSION["model"])) {
                  $content .= "{$_SESSION['model']}";
                  unset($_SESSION["model"]);
                } else $content .= "{$row['model']}";
                $content .= "\" required><br><br>
											Is the device working? <br>1 - Yes, 0 - No. <br><br>
											<input type=\"number\" name=\"active\" min=\"0\" max=\"1\" size=\"1\" value=\"";
                if (isset($_SESSION["active"])) {
                  $content .= "{$_SESSION['active']}";
                  unset($_SESSION["active"]);
                } else $content .= "{$row['active']}";
                $content .= "\" required><br><br>
											<button class=\"w3-button w3-black w3-round\">Submit</button></form>";
              }

            else
              throw new Exception("Error updating record: " . $db->error);
          } else {
            $content = "<input type=\"hidden\" name=\"action\" value=\"addInst\">Model:<br>
									<input type=\"text\" maxlength=\"30\" size=\"18\" name=\"model\" autocomplete=\"off\" ";
            if (isset($_SESSION["model"])) {
              $content .= " value=\"{$_SESSION['model']}\"";
              unset($_SESSION["model"]);
            }
            $content .= " required><br><br>
									Is the device working? <br>1 - Yes, 0 - No. <br><br>
									<input type=\"number\" name=\"active\" min=\"0\" max=\"1\" size=\"1\" value=\"";
            if (isset($_SESSION["active"])) {
              $content .= "{$_SESSION['active']}";
              unset($_SESSION["active"]);
            } else $content .= "1";
            $content .= "\" required><br><br>
									<button class=\"w3-button w3-black w3-round\">Submit</button></form>";
          }

          echo $content;

          $sql = "SELECT * FROM instruments ORDER BY idInstrument;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
            // output data of each row
            echo "<table class=\"w3-table-all w3-half w3-right\" style=\"max-width:350px;\">";
            echo "<tr><th>Id</th><th>Model</th><th>Active</th></tr>";

            while ($row = $result->fetch_assoc())
              echo "<tr><td><a href=\"admin.php?inst={$row['idInstrument']}\">{$row['idInstrument']}</a></td><td><a href=\"admin.php?inst={$row['idInstrument']}\">{$row['model']}</a></td><td><a href=\"admin.php?inst={$row['idInstrument']}\">{$row['active']}</a></td></tr>";

            echo "</table>";
          } else
            throw new Exception("Error updating record: " . $db->error);
        } catch (Exception $e) {
          $err1 = $e->getMessage();
        }
        ?>
        <div class="w3-panel"><br>
          <i class="w3-text-red">
            <?php if (isset($_SESSION["err1"])) {
              $err1 = $_SESSION["err1"];
              unset($_SESSION["err1"]);
            } else if (empty($_GET["inst"])) $err1 = "Please select an instrument or fill the form.";
            print $err1; ?>
          </i>
        </div>
      </div>

      <div class="w3-container hide" id="rig1">
        <?php
        try {
          echo "<form class=\"w3-container w3-half w3-left\" action=\"success.php\" method=\"post\">";

          if (isset($_GET["rig"])) {
            unset($_SESSION["inst"]);
            unset($_SESSION["user"]);

            $_SESSION["rig"] = $_GET["rig"];

            $sql = "SELECT rights.idRight, instruments.idInstrument, instruments.model, users.login, users.idUser FROM ((rights INNER JOIN users ON rights.idUser=users.idUser) INNER JOIN instruments ON rights.idInstrument=instruments.idInstrument) WHERE rights.idRight='{$_SESSION['rig']}' ORDER BY  rights.idRight;";
            $result = $db->query($sql);

            $content = "<input type=\"hidden\" name=\"action\" value=\"editRig\">
									Instrument & User:<br>";

            if ($db->affected_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $content .= "<select name=\"rig\" readonly required><option value=\"{$row['idRight']}\">'{$row['model']}' '{$row['login']}'</option></select>
											<input type=\"hidden\" name=\"cam\" value=\"{$row['idInstrument']}\">
											<input type=\"hidden\" name=\"use\" value=\"{$row['idUser']}\">";
              }
              $content .= "<br><br>
										Rights: <br>0 - no access, 1 - user, 2 - admin<br><br>
										<input type=\"number\" name=\"pow\" min=\"0\" max=\"2\" autofocus value=\"1\" size=\"1\" autofocus required><br><br>
										<button class=\"w3-button w3-black w3-round\">Submit</button></form>";
            }
          } else {
            $sql = "SELECT idInstrument, model FROM instruments WHERE active>0 ORDER BY idInstrument;";
            $result = $db->query($sql);
            $content = "<input type=\"hidden\" name=\"action\" value=\"addRig\">
									Instrument:<br>
									<select name=\"cam\" required>";

            if ($db->affected_rows > 0)
              while ($row = $result->fetch_assoc())
                $content .= "<option value=\"{$row['idInstrument']}\">{$row['model']}</option>";


            $sql = "SELECT idUser, login FROM users WHERE active>0 ORDER BY idUser;";
            $result = $db->query($sql);

            $content .= "</select><br><br>
									User:<br>
									<select name=\"use\" required>";

            if ($db->affected_rows > 0)
              while ($row = $result->fetch_assoc())
                $content .= "<option value=\"{$row['idUser']}\">{$row['login']}</option>";

            $content .= "</select><br><br>
									Rights: <br>0 - no access, 1 - user, 2 - admin<br><br>
									<input type=\"number\" name=\"pow\" min=\"0\" max=\"2\" value=\"1\" size=\"1\" required><br><br>
									<button class=\"w3-button w3-black w3-round\">Submit</button></form>";
          }

          echo $content;

          $sql = "SELECT rights.idRight, instruments.model, users.login, rights.power FROM ((rights INNER JOIN users ON rights.idUser=users.idUser) INNER JOIN instruments ON rights.idInstrument=instruments.idInstrument) ORDER BY  rights.idRight;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
            // output data of each row
            echo "<table class=\"w3-table-all w3-half w3-right\" style=\"max-width:350px;\">";
            echo "<tr><th>Id</th><th>User</th><th>Model</th><th>Power</th></tr>";

            while ($row = $result->fetch_assoc())
              echo "<tr><td><a href=\"admin.php?rig={$row['idRight']}\">{$row['idRight']}</a></td><td><a href=\"admin.php?rig={$row['idRight']}\">{$row['login']}</a></td><td><a href=\"admin.php?rig={$row['idRight']}\">{$row['model']}</a></td><td><a href=\"admin.php?rig={$row['idRight']}\">{$row['power']}</a></td></tr>";

            echo "</table>";
          } else
            throw new Exception("Error updating record: " . $db->error);
        } catch (Exception $e) {
          $err3 = $e->getMessage();
        }
        ?>
        <div class="w3-panel"><br>
          <i class="w3-text-red">
            <?php if (isset($_SESSION["err3"])) {
              $err3 = $_SESSION["err3"];
              unset($_SESSION["err3"]);
            } else if (empty($_GET["rig"])) $err3 = "Please select a right or fill the form.";
            print $err3; ?>
          </i>
        </div>
      </div>

      <div class="w3-container hide" id="user1">
        <?php
        try {
          echo "<form class=\"w3-half\" action=\"success.php\" method=\"post\">";

          if (isset($_GET["use"])) {
            unset($_SESSION["rig"]);
            unset($_SESSION["inst"]);

            $_SESSION["user"] = $_GET["use"];

            $sql = "SELECT * FROM users WHERE idUser='{$_SESSION['user']}' ORDER BY idUser;";
            $result = $db->query($sql);

            if ($db->affected_rows > 0)
              while ($row = $result->fetch_assoc()) {
                $content = "<input type=\"hidden\" name=\"action\" value=\"editUser\">First name:<br>
											<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"fname\" autofocus value=\"";
                if (isset($_SESSION["fname"])) {
                  $content .= "{$_SESSION['fname']}";
                  unset($_SESSION["fname"]);
                } else $content .= "{$row['firstname']}";
                $content .= "\" required><br><br>
											Last name:<br>
											<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"lname\" value=\"";
                if (isset($_SESSION["lname"])) {
                  $content .= "{$_SESSION['lname']}";
                  unset($_SESSION["lname"]);
                } else $content .= "{$row['lastname']}";
                $content .= "\" required><br><br>
											Login:<br>
											<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"login\" value=\"";
                if (isset($_SESSION["login"])) {
                  $content .= "{$_SESSION['login']}";
                  unset($_SESSION["login"]);
                } else $content .= "{$row['login']}";
                $content .= "\" required><br><br>
											Password:<br>
											<input type=\"password\" maxlength=\"20\" size=\"18\" name=\"password\" value=\"";
                if (isset($_SESSION["password"])) {
                  $content .= "{$_SESSION['password']}";
                  unset($_SESSION["password"]);
                } else $content .= "{$row['passwrd']}";
                $content .= "\" required> <br><br>
											E-mail:<br>
											<input type=\"email\" maxlength=\"30\" size=\"18\" name=\"email\" value=\"";
                if (isset($_SESSION["email"])) {
                  $content .= "{$_SESSION['email']}";
                  unset($_SESSION["email"]);
                } else $content .= "{$row['email']}";
                $content .= "\" required><br><br>
											Would the user remain\become active? <br>1 - Yes, 0 - No. <br><br>
											<input type=\"number\" min=\"0\" max=\"1\" name=\"active\" size=\"1\" value=\"";
                if (isset($_SESSION["active"])) {
                  $content .= "{$_SESSION['active']}";
                  unset($_SESSION["active"]);
                } else $content .= "{$row['active']}";
                $content .= "\" required><br><br>
											Would the user remain\become an admininstrator? <br>1 - Yes, 0 - No. <br><br>
											<input type=\"number\" min=\"0\" max=\"1\" name=\"admin\" size=\"1\" value=\"";
                if (isset($_SESSION["admin"])) {
                  $content .= "{$_SESSION['admin']}";
                  // unset($_SESSION["admin"]);
                } else $content .= "{$row['admin']}";
                $content .= "\" required><br><br>
											<button class=\"w3-button w3-black w3-round-large\">Submit</button></form>";
              }

            else
              throw new Exception("Error updating record: " . $db->error);
          } else {
            $content = "<input type=\"hidden\" name=\"action\" value=\"addUser\">
									First name:<br>
									<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"fname\" autocomplete=\"off\"";
            if (isset($_SESSION["fname"])) {
              $content .= " value=\"{$_SESSION['fname']}\"";
              unset($_SESSION["fname"]);
            }
            $content .= " required><br><br>
									Last name:<br>
									<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"lname\" autocomplete=\"off\"";
            if (isset($_SESSION["lname"])) {
              $content .= " value=\"{$_SESSION['lname']}\"";
              unset($_SESSION["lname"]);
            }
            $content .= " required><br><br>
									Login:<br>
									<input type=\"text\" maxlength=\"20\" size=\"18\" name=\"login\" autocomplete=\"off\"";
            if (isset($_SESSION["login"])) {
              $content .= " value=\"{$_SESSION['login']}\"";
              unset($_SESSION["login"]);
            }
            $content .= " required><br><br>
									Password:<br>
									<input type=\"password\" maxlength=\"20\" size=\"18\" name=\"password\" autocomplete=\"off\"";
            if (isset($_SESSION["password"])) {
              $content .= " value=\"{$_SESSION['password']}\"";
              unset($_SESSION["password"]);
            }
            $content .= " required> <br><br>
									E-mail:<br>
									<input type=\"email\" maxlength=\"30\" size=\"18\" name=\"email\" autocomplete=\"off\"";
            if (isset($_SESSION["email"])) {
              $content .= " value=\"{$_SESSION['email']}\"";
              unset($_SESSION["email"]);
            }
            $content .= " required><br><br>
									Will the user be active? <br>1 - Yes, 0 - No. <br><br>
									<input type=\"number\" min=\"0\" max=\"1\" name=\"active\" size=\"1\" value=\"";
            if (isset($_SESSION["active"])) {
              $content .= "{$_SESSION['active']}";
              unset($_SESSION["active"]);
            } else $content .= "1";
            $content .= "\" required><br><br>
									Will the user be an admininstrator? <br>1 - Yes, 0 - No. <br><br>
									<input type=\"number\" min=\"0\" max=\"1\" name=\"admin\" size=\"1\" value=\"";
            if (isset($_SESSION["admin"])) {
              $content .= "{$_SESSION['admin']}";
              // unset($_SESSION["admin"]);
            } else $content .= "0";
            $content .= "\" required><br><br>
									<button class=\"w3-button w3-black w3-round-large\">Submit</button></form>";
          }

          echo $content;


          $sql = "SELECT * FROM users ORDER BY idUser;";
          $result = $db->query($sql);

          if ($db->affected_rows > 0) {
            // output data of each row
            echo "<table class=\"w3-table-all w3-half\">";
            echo "<tr><th>Id</th><th>First name</th><th>Last name</th><th>Login</th><th>Active</th><th>e-mail</th><th>Admin</th></tr>";

            while ($row = $result->fetch_assoc())
              echo "<tr><td><a href=\"admin.php?use={$row['idUser']}\">{$row['idUser']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['firstname']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['lastname']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['login']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['active']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['email']}</a></td><td><a href=\"admin.php?use={$row['idUser']}\">{$row['admin']}</a></td><td></tr>";

            echo "</table>";
          } else
            throw new Exception("Error updating record: " . $db->error);
        } catch (Exception $e) {
          $err2 = $e->getMessage();
        }
        ?>
        <div class="w3-panel"><br>
          <i class="w3-text-red">
            <?php if (isset($_SESSION["err2"])) {
              $err2 = $_SESSION["err2"];
              unset($_SESSION["err2"]);
            } else if (empty($_GET["use"])) $err2 = "Please select a user or fill the form.";
            print $err2; ?>
          </i>
        </div>
      </div>
    </fieldset>
  </div>
  </main>

  <?php
  require_once("footer.php");
  ?>

</body>

</html>