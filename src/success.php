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

        $result = $db->query("SELECT id_user, admin FROM users WHERE username='{$user}' AND pass='{$pass}' AND active>0;");
        $row = $result->fetch_assoc();

        // if result matched $user and $pass, table row must be 1 row
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
      } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("location: login.php");
      }

      break;


    case "logAdm":
      try {
        if ($_POST["userType"] == "admin")
          header("location: admin.php");

        else {
          $result = $db->query("SELECT id_instrument FROM rights WHERE id_user='{$_SESSION['id_user']}' AND power>0 ORDER BY id_instrument LIMIT 1;");
          $row = $result->fetch_assoc();

          if ($db->affected_rows > 0) {
            $_SESSION["id_inst"] = $row['id_instrument'];
            header("location: profile.php?inst={$row['id_instrument']}");
          } else
            header("location: profile.php");
        }
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
            $row = $result->fetch_array(MYSQLI_ASSOC);

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
        $_SESSION["model"] = testInput($_POST["model"]);
        $_SESSION["active"] = testInput($_POST["active"][0]);

        if (preg_match("/[^-A-Za-z0-9_ ]/", $_SESSION["model"]))
          throw new Exception("Only models containing letters, numbers, hyphens and spaces are allowed");

        $sql = "SELECT model, active FROM instruments WHERE model='{$_SESSION['model']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE instruments SET model='{$_SESSION['model']}', active='{$_SESSION['active']}' WHERE model='{$_SESSION['model']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["model"]);
            unset($_SESSION["active"]);
            _output("editInst");
          } else
            throw new Exception("Error updating record: " . $db->error);
        } else {
          $sql = "INSERT INTO instruments (model, active) VALUES ('{$_SESSION['model']}', '{$_SESSION['active']}');";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["model"]);
            unset($_SESSION["active"]);
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
        $_SESSION["model"] = testInput($_POST["model"]);
        $_SESSION["active"] = testInput($_POST["active"][0]);

        if (preg_match("/[^-A-Za-z0-9_ ]/", $_SESSION["model"]))
          throw new Exception("Only models containing letters, numbers, hyphens and spaces are allowed");

        $sql = "SELECT model, active FROM instruments WHERE id_instrument='{$_SESSION['inst']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE instruments SET model='{$_SESSION['model']}', active='{$_SESSION['active']}' WHERE id_instrument='{$_SESSION['inst']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["inst"]);
            unset($_SESSION["model"]);
            unset($_SESSION["active"]);
            _output("editInst");
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
        $_SESSION["fname"] = testInput($_POST["fname"]);
        $_SESSION["lname"] = testInput($_POST["lname"]);
        $_SESSION["username"] = testInput($_POST["username"]);
        $_SESSION["email"] = testInput($_POST["email"]);
        $_SESSION["admin"] = testInput($_POST["admin"][0]);
        $_SESSION["active"] = testInput($_POST["active"][0]);
        $_POST["password"] = testInput($_POST["password"]);

        // check if name only contains letters and whitespace
        if (preg_match("/[^A-Za-z ]/", $_SESSION["fname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z ]/", $_SESSION["lname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z0-9_]/", $_SESSION["username"]))
          throw new Exception("Only logins containing letters, numbers and underscore are allowed");

        // check if e-mail address is well-formed
        if (!filter_var($_SESSION["email"], FILTER_VALIDATE_EMAIL))
          throw new Exception("Invalid email format");

        $sql = "SELECT username FROM users WHERE username='{$_SESSION['username']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0)
          throw new Exception("This username already exist in the database.");

        else {
          $sql = "INSERT INTO users (firstname, lastname, username, pass, active, email, admin) VALUES ('{$_SESSION['fname']}', '{$_SESSION['lname']}', '{$_SESSION['username']}', '{$_SESSION['password']}', '{$_SESSION['active']}', '{$_SESSION['email']}', '{$_SESSION['admin']}');";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["fname"]);
            unset($_SESSION["lname"]);
            unset($_SESSION["username"]);
            unset($_SESSION["email"]);
            unset($_SESSION["admin"]);
            unset($_SESSION["active"]);

            _output($_POST["action"]);
          } else
            throw new Exception("Error inserting record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err2"] = $e->getMessage();
        header("location: admin.php?er=1");
      }

      break;


    case "editUser":
      try {
        $_SESSION["fname"] = testInput($_POST["fname"]);
        $_SESSION["lname"] = testInput($_POST["lname"]);
        $_SESSION["username"] = testInput($_POST["username"]);
        $_SESSION["email"] = testInput($_POST["email"]);
        $_SESSION["admin"] = testInput($_POST["admin"][0]);
        $_SESSION["active"] = testInput($_POST["active"][0]);
        $_POST["password"] = testInput($_POST["password"]);

        // check if name only contains letters and whitespace
        if (preg_match("/[^A-Za-z ]/", $_SESSION["fname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z ]/", $_SESSION["lname"]))
          throw new Exception("Only names containing letters and white spaces are allowed");

        if (preg_match("/[^A-Za-z0-9_]/", $_SESSION["username"]))
          throw new Exception("Only logins containing letters, numbers and underscore are allowed");

        // check if e-mail address is well-formed
        if (!filter_var($_SESSION["email"], FILTER_VALIDATE_EMAIL))
          throw new Exception("Invalid email format");

        $sql = "SELECT username FROM users WHERE username='{$_SESSION['username']}' AND id_user!='{$_SESSION['id_user']}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0)
          throw new Exception("This username already exist in the database.");

        else {
          $sql = "UPDATE users SET firstname='{$_SESSION['fname']}', lastname='{$_SESSION['lname']}', username='{$_SESSION['username']}', pass='{$_POST['password']}', active='{$_SESSION['active']}', email='{$_SESSION['email']}', admin='{$_SESSION['admin']}' WHERE id_user='{$_SESSION['id_user']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["fname"]);
            unset($_SESSION["lname"]);
            unset($_SESSION["username"]);
            unset($_SESSION["email"]);
            unset($_SESSION["admin"]);
            unset($_SESSION["active"]);

            _output($_POST["action"]);
          } else
            throw new Exception("Error updating record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err2"] = $e->getMessage();
        header("location: admin.php?use={$_SESSION['id_user']}");
      }

      break;


    case "addRig":
      try {
        $idUse = testInput($_POST["use"]);
        $idIns = testInput($_POST["cam"]);
        $power = testInput($_POST["pow"]);

        $sql = "SELECT * FROM rights WHERE id_user='{$idUse}' AND id_instrument='{$idIns}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE rights SET id_user='{$idUse}', id_instrument='{$idIns}',  power='{$power}' WHERE id_instrument='{$idIns}' AND id_user='{$idUse}';";

          if ($db->query($sql) === TRUE) {
            _output("editRig");
          } else
            throw new Exception("Error updating record: " . $db->error);
        } else {
          $sql = "INSERT INTO rights (id_user, id_instrument, power) VALUES ('{$idUse}', '{$idIns}', '{$power}');";

          if ($db->query($sql) === TRUE)
            _output("addRig");

          else
            throw new Exception("Error inserting record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err4"] = $e->getMessage();
        header("location: admin.php");
      }

      break;


    case "editRig":
      try {
        $idUse = testInput($_POST["use"]);
        $idIns = testInput($_POST["cam"]);
        $power = testInput($_POST["pow"]);

        $sql = "SELECT * FROM rights WHERE id_user='{$idUse}' AND id_instrument='{$idIns}';";
        $result = $db->query($sql);

        if ($db->affected_rows > 0) {
          $sql = "UPDATE rights SET id_user='{$idUse}', id_instrument='{$idIns}',  power='{$power}' WHERE idRight='{$_SESSION['rig']}';";

          if ($db->query($sql) === TRUE) {
            unset($_SESSION["rig"]);
            _output($_POST["action"]);
          } else
            throw new Exception("Error updating record: " . $db->error);
        }
      } catch (Exception $e) {
        $_SESSION["err4"] = $e->getMessage();
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
      window.setTimeout(goTo, 1800);
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

</html>