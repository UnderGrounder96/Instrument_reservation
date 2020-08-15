<?php
require_once("../db/db.php");
require_once("functions.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST")
  switch ($_POST["action"]) {
    case "login":
      try {

        unset($_SESSION["admin"]);

        // To protect MySQL injection for Security purpose
        $user = testInput($_POST["username"]);
        $pass = testInput($_POST["password"]);

        if (preg_match("/[^A-Za-z0-9_]/", $user))
          throw new Exception("Your Username is invalid.");

        $result = $db->query("SELECT id_user, pass, admin FROM users WHERE username='{$user}' AND active>0;");
        $row = $result->fetch_assoc();

        if (password_verify($pass, $row["pass"])) {
          if ($db->affected_rows === 1) {
            $_SESSION["id_user"] = $row["id_user"];
            $_SESSION["login_user"] = $user;

            if ($row["admin"] == 1)
              $_SESSION["admin"] = $row["admin"];

            $result = $db->query("SELECT id_instrument FROM rights WHERE id_user='{$_SESSION['id_user']}' AND power>0 ORDER BY id_instrument LIMIT 1;");
            $row = $result->fetch_assoc();

            if (isset($row["id_instrument"])) {
              $_SESSION["id_inst"] = $row["id_instrument"];
              header("location: profile.php?inst={$row['id_instrument']}");
            } else
              header("location: profile.php");
          } else
            throw new Exception("Your Username or Password is invalid.");
        } else
          throw new Exception("Your Username or Password is invalid.");
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: login.php");
      }

      break;


    case "addRes":
      try {
        if (validateDate($_POST["dateIn"]) && validateDate($_POST["dateOut"])) {
          $curDate = date("Y-m-d");
          $nextTwoWeek = date("Y-m-d", strtotime("+2 week"));
          $date_in = date("Y-m-d", strtotime($_POST["dateIn"]));
          $date_out = date("Y-m-d", strtotime($_POST["dateOut"]));

          if ($date_in >= $curDate && $date_out >= $curDate) {
            if ($date_out >= $date_in && $nextTwoWeek >= $date_out) {

              $result = $db->query("SELECT id_reservation, date_in, date_out, id_user FROM reservations WHERE id_instrument='{$_SESSION['id_inst']}' AND DATE(date_out)>=CURDATE();");

              if ($db->affected_rows > 0)
                while ($row = $result->fetch_assoc()) {
                  if (_validateDates($row["date_in"], $row["date_out"], $date_in) && $row["id_user"] !== $_SESSION["id_user"]);
                  if (_validateDates($row["date_in"], $row["date_out"], $date_out) && $row["id_user"] !== $_SESSION["id_user"]);
                  if (_validateDates($date_in, $date_out, $row["date_out"]) && $row["id_user"] !== $_SESSION["id_user"]);
                  if (_validateDates($date_in, $date_out, $row["date_out"]) && $row["id_user"] !== $_SESSION["id_user"]);
                }

              $description = testInput($_POST["description"]);

              $result = $db->query("SELECT id_reservation, date_in, date_out FROM reservations WHERE id_instrument='{$_SESSION['id_inst']}' AND DATE(date_out)>=CURDATE() AND id_user='{$_SESSION['id_user']}';");
              $row = $result->fetch_assoc();

              if ($db->affected_rows > 0) {

                $sql = "UPDATE reservations SET date_in='{$date_in}', date_out='{$date_out}', description='{$description}' WHERE id_reservation='{$row['id_reservation']}';";

                if ($db->query($sql) === TRUE) {
                  _output("editRes");
                } else
                  throw new Exception("Error inserting record: " . $db->error);
              } else {
                $sql = "INSERT INTO reservations (date_in, date_out, description, id_instrument, id_user) VALUES ('{$date_in}', '{$date_out}', '{$description}', '{$_SESSION['id_inst']}', '{$_SESSION['id_user']}');";

                if ($db->query($sql) === TRUE) {
                  _output($_POST["action"]);
                } else
                  throw new Exception("Error inserting the record: " . $db->error);
              }
            } else
              throw new Exception("Please select appropriate dates. Within two weeks!");
          } else
            throw new Exception("Please select dates between today and within two weeks!");
        } else
          throw new Exception("Please veify the dates.");
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: profile.php?inst={$_SESSION['id_inst']}");
      }

      break;


    case "remRes":
      try {
        $sql = "DELETE FROM reservations WHERE id_reservation='{$_SESSION['id_res']}';";

        if ($db->query($sql) === TRUE)
          _output($_POST["action"]);

        else
          throw new Exception("Error inserting the record: " . $db->error);
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: profile.php?inst={$_SESSION['id_inst']}");
      }

      break;


    case "addInst":
      try {
        $_POST["model"] = testInput($_POST["model"]);
        $_POST["active"] = testInput($_POST["active"]);

        if (preg_match("/[^-A-Za-z0-9_ ]/", $_POST["model"]))
          throw new Exception("Only models containing letters, numbers, hyphens and spaces are allowed");

        $sql = "SELECT model, active FROM instruments WHERE model='{$_POST['model']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE instruments SET model='{$_POST['model']}', active='{$_POST['active']}' WHERE model='{$_POST['model']}';";

          if ($db->query($sql) === TRUE) {
            _output("editInst");
          } else
            throw new Exception("Error updating record: " . $db->error);
        } else {
          $sql = "INSERT INTO instruments (model, active) VALUES ('{$_POST['model']}', '{$_POST['active']}');";

          if ($db->query($sql) === TRUE) {
            _output($_POST["action"]);
          } else
            throw new Exception("Error inserting record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err1"] = $e->getMessage();
        header("location: admin.php?inst");
      }

      break;


    case "addUser":
      try {
        $_POST["fname"] = testInput($_POST["fname"]);
        $_POST["lname"] = testInput($_POST["lname"]);
        $_POST["username"] = testInput($_POST["username"]);
        $_POST["email"] = testInput($_POST["email"]);
        $_POST["admin"] = testInput($_POST["admin"]);
        $_POST["active"] = testInput($_POST["active"]);
        $_POST["password"] = testInput($_POST["password"]);

        // check if name only contains letters and whitespace
        if (preg_match("/[^A-Za-z ]/", $_POST["fname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z ]/", $_POST["lname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z0-9_]/", $_POST["username"]))
          throw new Exception("Only logins containing letters, numbers and underscore are allowed");

        // check if e-mail address is well-formed
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
          throw new Exception("Invalid email format");

        $sql = "SELECT username FROM users WHERE username='{$_POST['username']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0)
          throw new Exception("This username already exist in the database.");

        else {
          $_POST["password"] = password_hash($_POST["password"], PASSWORD_BCRYPT);
          $sql = "INSERT INTO users (first_name, last_name, username, pass, email, admin, active) VALUES ('{$_POST['fname']}', '{$_POST['lname']}', '{$_POST['username']}', '{$_POST['password']}', '{$_POST['email']}', '{$_POST['admin']}', '{$_POST['active']}');";

          unset($_POST["password"]);

          if ($db->query($sql) === TRUE) {
            _output($_POST["action"]);
          } else
            throw new Exception("Error inserting record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err3"] = $e->getMessage();
        header("location: admin.php");
      }

      break;


    case "editUser":
      try {
        $_POST["fname"] = testInput($_POST["fname"]);
        $_POST["lname"] = testInput($_POST["lname"]);
        $_POST["username"] = testInput($_POST["username"]);
        $_POST["email"] = testInput($_POST["email"]);
        $_POST["admin"] = testInput($_POST["admin"]);
        $_POST["active"] = testInput($_POST["active"]);
        $_POST["id_user"] = testInput($_POST["id_user"]);

        // check if name only contains letters and whitespace
        if (preg_match("/[^A-Za-z ]/", $_POST["fname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z ]/", $_POST["lname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z0-9_]/", $_POST["username"]))
          throw new Exception("Only logins containing letters, numbers and underscore are allowed");

        // check if e-mail address is well-formed
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
          throw new Exception("Invalid email format");

        $sql = "SELECT username FROM users WHERE username='{$_POST['username']}' AND id_user!='{$_POST['id_user']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0)
          throw new Exception("This username already exist in the database.");

        else {
          $sql = "UPDATE users SET first_name='{$_POST['fname']}', last_name='{$_POST['lname']}', username='{$_POST['username']}', email='{$_POST['email']}', admin='{$_POST['admin']}', active='{$_POST['active']}' WHERE id_user='{$_POST['id_user']}';";

          if ($db->query($sql) === TRUE) {
            _output($_POST["action"]);
          } else
            throw new Exception("Error updating record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err3"] = $e->getMessage();
        header("location: admin.php?use={$_POST['id_user']}");
      }

      break;


    case "addRig":
      try {
        $_POST["user"] = testInput($_POST["user"]);
        $_POST["inst"] = testInput($_POST["inst"]);
        $_POST["pow"] = testInput($_POST["pow"]);

        $sql = "SELECT * FROM rights WHERE id_user='{$_POST['user']}' AND id_instrument='{$_POST['inst']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE rights SET id_user='{$_POST['user']}', id_instrument='{$_POST['inst']}',  power='{$_POST['pow']}' WHERE id_instrument='{$_POST['inst']}' AND id_user='{$_POST['user']}';";

          if ($db->query($sql) === TRUE) {
            _output("editRig");
          } else
            throw new Exception("Error updating record: " . $db->error);
        } else {
          $sql = "INSERT INTO rights (id_user, id_instrument, power) VALUES ('{$_POST['user']}', '{$_POST['inst']}', '{$_POST['pow']}');";

          if ($db->query($sql) === TRUE)
            _output($_POST["action"]);

          else
            throw new Exception("Error inserting record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err2"] = $e->getMessage();
        header("location: admin.php?rig={$_SESSION['rig']}");
      }

      break;
  }
?>

<!DOCTYPE HTML>
<html>

<head>
  <script>
    function loaded() {
      window.setTimeout(goTo, 2000);
    }

    function goTo() {
      <?php
      switch ($_POST["action"]) {
        case "addInst":
        case "editInst":

        case "addUser":
        case "editUser":

        case "addRig":
        case "editRig":
          echo "window.location.replace(\"admin.php\");";
          break;

        case "addRes":
        case "editRes":
        case "remRes":
          echo "window.location.replace(\"profile.php?inst={$_SESSION['id_inst']}\");";
          break;
      }
      ?>
    }
  </script>
</head>

<?php
header("refresh:2; url=logout.php");
?>

</html>