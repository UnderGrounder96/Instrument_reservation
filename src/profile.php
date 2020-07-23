<!DOCTYPE html>
<html>

<?php
require_once("session.php");

$title_page = "Profile";

require_once("head.php");

static $err1 = "";
?>

<body>
  <noscript> Please turn on JavaScript or change browsers!</noscript>

  <link rel="stylesheet" href="../public/css/calendar.css" />

  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">

    <div class="container">
      <div class="table-responsive">
        <?php
        $result =
          $db->query(
            "SELECT rights.id_instrument, instruments.model
            FROM rights JOIN instruments
            ON rights.id_instrument=instruments.id_instrument
            WHERE rights.id_user='{$_SESSION['id_user']}'
            AND rights.power>0 AND instruments.active>0
            ORDER BY rights.id_instrument;"
          );

        if ($db->affected_rows > 0) {
        ?>
          <table class="table-sm table table-hover justify-left" style="width:150px">
            <thead>
              <tr>
                <th>Id</th>
                <th>Model</th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = $result->fetch_assoc())
                echo "<tr><td><a href=\"profile.php?inst={$row['id_instrument']}\">{$row['id_instrument']}</a></td><td><a href=\"profile.php?inst={$row['id_instrument']}\">{$row['model']}</a></td></tr>";
              ?>
            </tbody>

          </table>
        <?php
        }

        $result = $db->query("
          SELECT id_reservation, date_in, date_out, id_instrument
          FROM Reservations
          WHERE id_user='{$_SESSION['id_user']}'
          AND DATE(date_out)>=CURDATE()
          ORDER BY id_reservation;
        ");

        if ($db->affected_rows > 0) {
          // output data of each row
          echo "
          <table class=\"table-sm table table-hover justify-left\" style=\"max-width:530px\">
          <tr>
            <th>Id</th>
            <th>Date in</th>
            <th>Date out</th>
            <th>Id instrument</th>
          </tr>
          ";

          while ($row = $result->fetch_assoc())
            echo "<tr>
            <td>{$row['id_reservation']}</td>
            <td>{$row['date_in']}</td>
            <td>{$row['date_out']}</td>
            <td>{$row['id_instrument']}</td>
          </tr>";

          echo "</table>";
        }
        ?>
      </div>

      <div class="container" style="position:relative;margin:auto;display:block;float-left"><br>
        <?php
        if (isset($_GET["inst"]))
          $_SESSION["idInst"] = $_GET["inst"];

        require "calendar.php";
        $calendar = new Calendar();
        echo $calendar->show() . "<br>";

        if (isset($_GET["inst"])) {
          $result = $db->query("SELECT id_instrument FROM rights WHERE id_user='{$_SESSION['id_user']}' AND id_instrument='{$_SESSION['idInst']}' AND power>1");
          if ($db->affected_rows > 0)
            echo "<p><nav class=\"w3-center h\"><b><span id=\"res\">Add/Rem res.</span> | <span id=\"res2\">Change res.</span></nav></b></p>";
        }

        if (isset($_GET["res"])) {
          $result = $db->query("SELECT reservations.id_instrument FROM reservations JOIN rights ON reservations.id_instrument=rights.id_instrument WHERE reservations.id_reservation='{$_GET['res']}' AND rights.id_user='{$_SESSION['id_user']}'AND rights.power>1;");
          if ($db->affected_rows > 0)
            echo "<p><nav class=\"w3-center h\"><b><span id=\"res\">Add/Rem res.</span> | <span id=\"res2\">Change res.</span></nav></b></p>";
        }
        ?>
      </div>
    </div>

    <div class="container">
      <div id="res1" class="hide">
        <fieldset>
          <legend>Please fill the form:</legend>
          <form class="w3-container w3-half" action="success.php" method="post">
            <input type="hidden" name="action" value="addRes">
            Instrument:<br>
            <?php
            if (isset($_GET["inst"])) {
              $result = $db->query("SELECT rights.id_instrument, instruments.model FROM rights JOIN instruments ON rights.id_instrument=instruments.id_instrument WHERE rights.id_user='{$_SESSION['id_user']}'AND rights.id_instrument='{$_GET['inst']}' AND rights.power>0 AND instruments.active>0;");

              if ($db->affected_rows > 0) {
                $select = "<select name=\"id_instrument\" required>";

                while ($row = $result->fetch_assoc())
                  $select .= "<option value=\"{$row['id_instrument']}\">{$row['model']}</option>";

                $select .= "</select>";

                echo $select;
              }
            }
            ?><br><br>

            Date in: <br><input type="datetime-local" name="dateIn" required><br><br>
            Date out: <br><input type="datetime-local" name="dateOut" required><br><br>
            Description: <br><textarea name="description" maxlength="50" rows="3" cols="25" required></textarea>
            <br><span id="chars">50</span> characters remaining...<br><br>
            <button class="w3-button w3-black w3-round">Submit</button>

            <div class="w3-panel">
              <i class="w3-text-red">
                <?php
                if (isset($_SESSION["error"]))
                  print $_SESSION["error"];
                else if (empty($_GET["inst"])) {
                  $_SESSION["error"] = "Please select an instrument.";
                  print $_SESSION["error"];
                }
                unset($_SESSION["error"]); ?>
              </i>
            </div>
          </form>


          <form class="w3-container w3-half" action="success.php" method="post">
            <input type="hidden" name="action" value="remRes">
            <?php
            if (isset($_GET["inst"])) {
              $result = $db->query("SELECT power FROM rights WHERE id_user='{$_SESSION['id_user']}' AND id_instrument='{$_SESSION['idInst']}';");
              $row = $result->fetch_assoc();

              if ($row["power"] > 1) {
                $sql = "SELECT reservations.id_reservation, reservations.date_in, reservations.date_out, users.login FROM reservations JOIN users ON reservations.id_user=users.id_user WHERE id_instrument='{$_SESSION['idInst']}' AND date_out>=CURDATE() ORDER BY reservations.id_reservation;";
                $result = $db->query($sql);

                if ($db->affected_rows > 0) {
                  echo "Which reservations would you like to delete:<br>";

                  $select = "<select name=\"id_reservation\" required>";

                  while ($row = $result->fetch_assoc())
                    $select .= "<option value=\"{$row['id_reservation']}\">" . date('Y/m/d', strtotime($row['date_in'])) . "-" . date('d', strtotime($row['date_out'])) . " '{$row['login']}'</option>";

                  $select .= "</select><br>";

                  echo $select . "<br>";

                  echo "<button class=\"w3-button w3-black w3-round-large\">Delete</button>";
                }
              } else {
                $sql = "SELECT id_reservation, date_in, date_out FROM reservations WHERE id_instrument='{$_SESSION['idInst']}' AND id_user='{$_SESSION['id_user']}' AND date_out>=CURDATE() ORDER BY reservations.id_reservation;";
                $result = $db->query($sql);

                if ($db->affected_rows > 0) {
                  echo "Which reservations would you like to delete:<br>";

                  $select = "<select name=\"id_reservation\" required>";

                  while ($row = $result->fetch_assoc())
                    $select .= "<option value=\"{$row['id_reservation']}\">" . date('Y/m/d', strtotime($row['date_in'])) . "-" . date('d', strtotime($row['date_out'])) . "</option>";

                  $select .= "</select><br>";

                  echo $select . "<br>";

                  echo "<button class=\"w3-button w3-black w3-round-large\">Delete</button>";
                }
              }
            }
            ?>
          </form>

          <div class="w3-panel">
            <i class="w3-text-red">
              <?php if (isset($_SESSION["error"])) print $_SESSION["error"];
              unset($_SESSION["error"]); ?>
            </i>
          </div>
        </fieldset>
      </div>

      <div id="res3" class="hide h">
        <fieldset>
          <legend>Please fill the form:</legend>
          <?php
          try {
            if (isset($_GET["res"])) {
              $_SESSION["res"] = $_GET["res"];

              $sql = "SELECT reservations.id_instrument FROM reservations JOIN rights ON reservations.id_instrument=rights.id_instrument WHERE reservations.id_reservation='{$_GET['res']}' AND rights.id_user='{$_SESSION['id_user']}'AND rights.power>1;";
              $result = $db->query($sql);

              if ($db->affected_rows > 0) {
                $sql = "SELECT * FROM reservations WHERE id_reservation='{$_SESSION['res']}' ORDER BY id_reservation;";
                $result = $db->query($sql);

                if ($db->affected_rows > 0)
                  while ($row = $result->fetch_assoc()) {
                    $content = "<form class=\"w3-container w3-half w3-left\" action=\"success.php\" method=\"post\"><input type=\"hidden\" name=\"action\" value=\"editRes\">
                                            Date in: <br><input type=\"text\" name=\"dateIn1\" maxlength=\"19\" value=\"";
                    if (isset($_SESSION["date_in"])) {
                      $content .= "{$_SESSION['date_in']}";
                      unset($_SESSION["date_in"]);
                    } else $content .= "{$row['date_in']}";
                    $content .= "\" required><br><br>
                                            Date out: <br><input type=\"text\" name=\"dateOut1\" maxlength=\"19\" value=\"";
                    if (isset($_SESSION["date_out"])) {
                      $content .= "{$_SESSION['date_out']}";
                      unset($_SESSION["date_out"]);
                    } else $content .= "{$row['date_out']}";
                    $content .= "\" required><br><br>
                                            Description: <br><input id=\"desc1\" type=\"text\" name=\"description\" maxlength=\"50\" value=\"";
                    if (isset($_SESSION["description"])) {
                      $content .= "{$_SESSION['description']}";
                      unset($_SESSION["description"]);
                    } else $content .= "{$row['description']}";
                    $content .= "\" rows=\"3\" cols=\"25\" required>
                                            <br><span id=\"chars1\">50</span> characters remaining...<br><br>
                                            <button class=\"w3-button w3-black w3-round\">Submit</button></form>";
                  }
                else
                  throw new Exception("Error updating record: " . $db->error);

                echo $content;
              }
            }

            if (isset($_GET["inst"])) {
              $result = $db->query("SELECT power FROM rights WHERE id_user='{$_SESSION['id_user']}' AND id_instrument='{$_GET['inst']}';");
              $row = $result->fetch_assoc();

              if ($row["power"] > 1) {
                $result = $db->query("SELECT reservations.id_reservation, reservations.date_in, reservations.date_out, users.login, reservations.description FROM reservations JOIN users ON reservations.id_user=users.id_user WHERE reservations.id_instrument='{$_GET['inst']}' AND date_out>=CURDATE() ORDER BY reservations.id_reservation;");

                if ($db->affected_rows > 0) {
                  // output data of each row
                  echo "<table class=\"w3-table-all w3-half w3-right\">";
                  echo "<tr><th>Date in</th><th>Date out</th><th>Login user</th><th>Description</th></tr>";

                  while ($row = $result->fetch_assoc())
                    echo "<tr><td><a href=\"user.php?res={$row['id_reservation']}\">{$row['date_in']}</a></td><td><a href=\"user.php?res={$row['id_reservation']}\">{$row['date_out']}</a></td><td><a href=\"user.php?res={$row['id_reservation']}\">{$row['login']}</a></td><td><a href=\"user.php?res={$row['id_reservation']}\">{$row['description']}</a></td></tr>";

                  echo "</table>";
                } else
                  throw new Exception("Error updating record: " . $db->error);
              }
            }
          } catch (Exception $e) {
            $err1 = $e->getMessage();
          }
          ?>
          <div class="w3-panel"><br>
            <i class="w3-text-red">
              <?php if (isset($_SESSION["err1"])) {
                $err1 = $_SESSION["err1"];
                unset($_SESSION["err1"]);
              } else if (empty($_GET["res"])) $err1 = "Please select a reservation and fill the form.";
              print $err1; ?>
            </i>
          </div>
        </fieldset>
      </div>
    </div>
  </main>

  <?php
  require_once("footer.php");

  if (isset($_GET["use"]) || isset($_GET["er"]))
    echo '<script>jQuery("#user1").show();</script>';

  else if (isset($_GET["rig"]))
    echo '<script>jQuery("#rig1").show()</script>';

  else if (isset($_GET["inst"]))
    echo '<script>jQuery("#inst1").show();</script>';
  ?>

  <script type="text/javascript">
    $.noConflict();

    jQuery(function() {
      var maxLength = 50;
      jQuery(".hide").hide();
      <?php
      $result = $db->query("SELECT power FROM rights WHERE id_user='{$_SESSION['id_user']}';");
      $row = $result->fetch_assoc();

      if ($row["power"] > 0)
        if (isset($_GET["inst"]))
          echo "
            jQuery(\".hide\").hide();\n
            jQuery(\"#res1\").show();
          ";

        else if (isset($_GET["res"])) {
          $result = $db->query("SELECT reservations.id_instrument FROM reservations JOIN rights ON reservations.id_instrument=rights.id_instrument WHERE reservations.id_reservation='{$_GET['res']}' AND rights.id_user='{$_SESSION['id_user']}'AND rights.power>1;");
          $row = $result->fetch_assoc();

          if ($db->affected_rows > 0)
            echo "jQuery(\".hide\").hide();\n
                jQuery(\"#res3\").show();\n

                jQuery(\"#desc1\").keyup(function() {
                  var length = jQuery(this).val().length;
                  var length = maxLength-length;
                  jQuery(\"#chars1\").text(length);
                });";

          else
            echo "jQuery(\".hide\").hide();\n
                jQuery(\".h\").empty();";
        }

      ?>
      jQuery("#res").click(function() {
        jQuery(".hide").hide();
        jQuery("#res1").show();
      });

      jQuery("#res2").click(function() {
        jQuery(".hide").hide();
        jQuery("#res3").show();
      });

      jQuery(".dates").click(function() {
        jQuery("#res1").toggle();
      });

      jQuery("textarea").keyup(function() {
        var length = jQuery(this).val().length;
        var length = maxLength - length;
        jQuery("#chars").text(length);
      });
    });
  </script>
</body>

</html>