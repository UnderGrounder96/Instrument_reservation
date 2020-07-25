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
        $dateIn = date("Y-m-d", strtotime($_POST["dateIn"]));
        $dateOut = date("Y-m-d", strtotime($_POST["dateOut"]));

        if ($dateOut >= $dateIn) {
          if ($dateIn >= date("Y-m-d") && $dateOut >= date("Y-m-d")) {
            $sql = "SELECT date_in, date_out FROM reservations WHERE id_instrument='{$_SESSION['id_inst']}' AND DATE(date_out)>=CURDATE();";
            $result = $db->query($sql);

            if ($db->affected_rows > 0)
              while ($row = $result->fetch_assoc()) {
                if (date("Y-m-d", strtotime($row["date_in"])) <= $dateIn && date("Y-m-d", strtotime($row["date_out"])) >= $dateIn)
                  throw new Exception("Dates already taken, please select another dates.");

                if (date("Y-m-d", strtotime($row["date_in"])) <= $dateOut && date("Y-m-d", strtotime($row["date_out"])) >= $dateOut)
                  throw new Exception("Dates already taken, please select other dates.");
              }

            $date_in = date("Y-m-d H:i", strtotime($_POST["dateIn"]));
            $date_out = date("Y-m-d H:i", strtotime($_POST["dateOut"]));
            $description = testInput($_POST["description"]);


            $sql = "INSERT INTO reservations (date_in, date_out, description, id_instrument, id_user) VALUES ('{$date_in}', '{$date_out}', '{$description}', '{$_POST['id_instrument']}', '{$_SESSION['id_user']}');";

            if ($db->query($sql) === TRUE) {
              //unset($_SESSION["id_inst"]);
              _output($_POST["action"]);
            } else
              throw new Exception("Error inserting the record: " . $db->error);
          } else
            throw new Exception("Please verify the dates and time.");
        } else
          throw new Exception("Please select appropriate dates.");
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: user.php?inst={$_SESSION['id_inst']}");
      }

      break;


    case "editRes":
      try {
        if (validateDate($_POST["dateIn1"]) && validateDate($_POST["dateOut1"])) {
          $_SESSION["date_in"] = testInput(date("Y-m-d H:i:s", strtotime($_POST["dateIn1"])));
          $_SESSION["date_out"] = testInput(date("Y-m-d H:i:s", strtotime($_POST["dateOut1"])));

          if ($_SESSION["date_out"] >= $_SESSION["date_in"]) {
            $sql = "SELECT id_instrument FROM reservations WHERE id_reservation='{$_SESSION['res']}';";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();

            $_SESSION["id_inst"] = $row["id_instrument"];

            if ($db->affected_rows > 0) {
              $sql = "SELECT id_reservation, date_in, date_out FROM reservations WHERE id_instrument='{$_SESSION['id_inst']}' AND DATE(date_out)>=CURDATE();";
              $result = $db->query($sql);

              if ($db->affected_rows > 0)
                while ($row = $result->fetch_assoc()) {
                  if (date("Y-m-d", strtotime($row["date_in"])) <= $_SESSION["date_in"] && date("Y-m-d", strtotime($row["date_out"])) >= $_SESSION["date_in"] && $row["id_reservation"] != $_SESSION['res'])
                    throw new Exception("Dates already taken, please select another dates.");

                  if (date("Y-m-d", strtotime($row["date_in"])) <= $_SESSION["date_out"] && date("Y-m-d", strtotime($row["date_out"])) >= $_SESSION["date_out"] && $row["id_reservation"] != $_SESSION['res'])
                    throw new Exception("Dates already taken, please select other dates.");
                }

              $_SESSION["description"] = testInput($_POST["description"]);

              $sql = "UPDATE reservations SET date_in='{$_SESSION['date_in']}', date_out='{$_SESSION['date_out']}', description='{$_SESSION['description']}' WHERE id_reservation='{$_SESSION['res']}';";

              if ($db->query($sql) === TRUE) {
                unset($_SESSION["res"]);
                //unset($_SESSION["id_inst"]);
                unset($_SESSION["date_in"]);
                unset($_SESSION["date_out"]);
                unset($_SESSION["description"]);

                _output($_POST["action"]);
              } else
                throw new Exception("Error inserting record: " . $db->error);
            }
          } else
            throw new Exception("Please select appropriate dates.");
        } else
          throw new Exception("Please veify the dates.");
      } catch (Exception $e) {
        $_SESSION["err1"] = $e->getMessage();
        header("location: user.php?res={$_SESSION['res']}");
      }

      break;


    case "remRes":
      try {
        $sql = "DELETE FROM reservations WHERE id_reservation='{$_POST['id_reservation']}';";

        if ($db->query($sql) === TRUE)
          _output($_POST["action"]);

        else
          throw new Exception("Error inserting the record: " . $db->error);
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: user.php?inst={$_SESSION['id_inst']}");
      }

      break;


    case "addInst":
      try {
        $_POST["model"] = testInput($_POST["model"]);
        $_POST["active"] = testInput($_POST["active"][0]);

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
        header("location: admin.php");
      }

      break;


    case "editInst":
      try {
        $_POST["model"] = testInput($_POST["model"]);
        $_POST["active"] = testInput($_POST["active"][0]);

        if (preg_match("/[^-A-Za-z0-9_ ]/", $_POST["model"]))
          throw new Exception("Only models containing letters, numbers, hyphens and spaces are allowed");

        $sql = "SELECT model, active FROM instruments WHERE id_instrument='{$_SESSION['inst']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE instruments SET model='{$_POST['model']}', active='{$_POST['active']}' WHERE id_instrument='{$_SESSION['inst']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["inst"]);

            _output($_POST["action"]);
          } else
            throw new Exception("Error updating record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err1"] = $e->getMessage();
        header("location: admin.php?inst={$_SESSION['inst']}");
      }

      break;


    case "addUser":
      try {
        $_POST["fname"] = testInput($_POST["fname"]);
        $_POST["lname"] = testInput($_POST["lname"]);
        $_POST["username"] = testInput($_POST["username"]);
        $_POST["email"] = testInput($_POST["email"]);
        $_POST["admin"] = testInput($_POST["admin"][0]);
        $_POST["active"] = testInput($_POST["active"][0]);
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
        $_POST["admin"] = testInput($_POST["admin"][0]);
        $_POST["active"] = testInput($_POST["active"][0]);

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


    case "editRig":
      try {
        $_POST["use"] = testInput($_POST["use"]);
        $_POST["inst"] = testInput($_POST["inst"]);
        $_POST["pow"] = testInput($_POST["pow"][0]);

        $sql = "SELECT * FROM rights WHERE id_user='{$_POST['use']}' AND id_instrument='{$_POST['inst']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE rights SET id_user='{$_POST['use']}', id_instrument='{$_POST['inst']}',  power='{$_POST['pow']}' WHERE id_instrument='{$_POST['inst']}' AND id_user='{$_POST['use']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["rig"]);
            _output($_POST["action"]);
          } else
            throw new Exception("Error updating record: " . $db->error);
        } else {
          $sql = "INSERT INTO rights (id_user, id_instrument, power) VALUES ('{$_POST['use']}', '{$_POST['inst']}', '{$_POST['pow']}');";

          if ($db->query($sql) === TRUE)
            _output("addRig");

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
header("refresh:3; url=logout.php");
?>

</html>