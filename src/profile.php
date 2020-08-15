<!DOCTYPE html>
<html>

<?php
require_once("session.php");
require_once("functions.php");

$title_page = "Profile";

require_once("head.php");

static $err1 = "";
?>

<body class="container shadow">
  <noscript> Please turn on JavaScript or change browsers!</noscript>

  <link rel="stylesheet" href="../public/css/calendar.css" />

  <?php
  require_once("header.php");
  ?>

  <main class="container" role="main">

    <div class="container">
      <div class="container row">
        <div class="col-sm-3 mt-4 container table-responsive-sm mr-auto text-center">
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
            <table class="table-sm table shadow table-hover">
              <thead>
                <tr>
                  <th>Model</th>
                </tr>
              </thead>

              <tbody>
                <?php
                while ($row = $result->fetch_assoc())
                  echo "<tr><td><a href=\"profile.php?inst={$row['id_instrument']}\">{$row['model']}</a></td></tr>";
                ?>
              </tbody>
            </table>

          <?php
          }
          ?>
        </div>


        <?php
        $result = $db->query(
          "SELECT Reservations.id_reservation, Reservations.date_in, Reservations.date_out, Instruments.model
          FROM Reservations JOIN Instruments ON Reservations.id_instrument=Instruments.id_instrument
          WHERE DATE(Reservations.date_out)>=CURDATE()
          AND Reservations.id_user='{$_SESSION['id_user']}';"
        );

        if ($db->affected_rows > 0) {

        ?>
          <div class="col mt-4 container table-responsive text-center">
            <table class="table-sm table shadow table-hover">
              <thead>
                <tr>
                  <th>Model</th>
                  <th>Date in</th>
                  <th>Date out</th>
                </tr>
              </thead>

              <tbody>
              <?php
              while ($row = $result->fetch_assoc())
                echo "
            <tr>
              <td><a href=\"profile.php?res={$row['id_reservation']}\">{$row['model']}</a></td>
              <td><a href=\"profile.php?res={$row['id_reservation']}\">{$row['date_in']}</a></td>
              <td><a href=\"profile.php?res={$row['id_reservation']}\">{$row['date_out']}</a></td>
            </tr>";
            }
              ?>
              </tbody>
            </table>
          </div>
      </div>


      <div class="d-block m-auto container">
        <?php
        if (isset($_GET["inst"]))
          $_SESSION["id_inst"] = testInput($_GET["inst"]);

        if (isset($_GET["res"]))
          $_SESSION["id_res"] = testInput($_GET["res"]);

        require_once("calendar.php");
        $calendar = new Calendar();

        echo $calendar->show() . "<br />";

        if (isset($_SESSION["error"])) {
          echo '
          <div class="alert alert-danger mt-3 w-50" role="alert">
            <em>'
            . $_SESSION["error"] .
            '</em>
          </div>';

          unset($_SESSION["error"]);
        } else if (empty($_GET["inst"]) && !isset($_GET["res"])) {
          echo '
          <div class="alert alert-secondary w-25 mt-3" role="alert">
            <em>
            Please select an instrument.
            </em>
          </div>';
        }
        ?>
      </div>


      <div class="container row">
        <div class="container hide col" id="inst1">
          <?php
          if (isset($_GET["inst"])) {
          ?>
            <form class="container" action="success.php" method="post">
              <input type="hidden" name="action" value="addRes">

              <h4 class="text-on-pannel text-primary text-uppercase">
                <strong> Please fill the form: </strong>
              </h4>

              <div class="control-group">
                <label>Instrument:
                  <select class="form-control" name="id_instrument" readonly required>
                    <?php

                    $result = $db->query(
                      "SELECT rights.id_instrument, instruments.model FROM rights
                    JOIN instruments ON rights.id_instrument=instruments.id_instrument
                    WHERE rights.id_user='{$_SESSION['id_user']}'
                    AND rights.id_instrument='{$_SESSION['id_inst']}'
                    AND rights.power>0 AND instruments.active>0;"
                    );

                    if ($db->affected_rows > 0)
                      while ($row = $result->fetch_assoc())
                        echo "<option value=\"{$row['id_instrument']}\">{$row['model']}</option>";
                    ?>
                  </select>
                </label>
              </div>

              <div class="control-group">
                <label>Date in:
                  <input type="date" class="form-control date" name="dateIn" required>
                </label>
              </div>

              <div class="control-group">
                <label>Date out:
                  <input type="date" class="form-control date" name="dateOut" required>
                </label>
              </div>

              <div class="control-group">
                <label>Description:
                  <textarea name="description" class="form-control" maxlength="90" rows="3" cols="30" required></textarea>
                  <em><span id="chars">90</span> characters remaining...</em>
                </label>
              </div>

              <div class="form-group mt-2">
                <input type="Submit" class="form-control btn btn-outline-success btn-block btn-sm" value="Add" style="width:90px" />
              </div>
            </form>
          <?php
          }
          ?>
        </div>

        <div class="container hide col" id="res1">
          <?php
          if (isset($_GET["res"])) {
            $result = $db->query(
              "SELECT * FROM Reservations
              JOIN Instruments ON Reservations.id_instrument=Instruments.id_instrument
            WHERE id_reservation='{$_SESSION['id_res']}';"
            );

            if ($row = $result->fetch_assoc()) {
          ?>
              <form class="container" action="success.php" method="post">
                <input type="hidden" name="action" value="remRes">

                <h4 class="text-on-pannel text-danger text-uppercase">
                  <strong> Please fill the form: </strong>
                </h4>

                <div class="control-group">
                  <label>Instrument:
                    <select class="form-control" name="id_instrument" readonly required>
                      <?php
                      echo "<option value=\"{$row['id_instrument']}\">{$row['model']}</option>";
                      ?>
                    </select>
                  </label>
                </div>

                <div class="control-group">
                  <label>Date in:

                    <?php
                    echo "<input type=\"date\" class=\"form-control\" name=\"dateIn\" value=\"{$row['date_in']}\" readonly required>";
                    ?>
                  </label>
                </div>

                <div class="control-group">
                  <label>Date out:
                    <?php
                    echo "<input type=\"date\" class=\"form-control\" name=\"dateOut\" value=\"{$row['date_out']}\" readonly required>";
                    ?>
                  </label>
                </div>

                <div class="form-group mt-2">
                  <input type="Submit" class="form-control btn btn-outline-danger btn-block btn-sm" value="Remove" style="width:90px" />
                </div>
              </form>
          <?php
            }
          }
          ?>
        </div>
      </div>

    </div>
  </main>

  <?php
  require_once("footer.php");
  ?>

  <script>
    jQuery(() => {
      let maxLength = 90;

      jQuery(".date").attr("min", _getDate()).attr("max", _getDate(Date.now() + 12096e5)).val(_getDate());

      <?php
      if (isset($_GET["inst"])) {
        $result = $db->query(
          "SELECT * FROM rights
          WHERE id_user='{$_SESSION['id_user']}'
          AND id_instrument='{$_SESSION['id_inst']}'
          AND power > 0;"
        );

        if ($db->affected_rows > 0)
          echo "
            jQuery(\"#inst1\").show();
          ";
      } else if (isset($_GET["res"])) {
        $result = $db->query(
          "SELECT * FROM reservations
          WHERE id_user='{$_SESSION['id_user']}'
          AND id_reservation='{$_SESSION['id_res']}'
          AND DATE(date_out)>=CURDATE();"
        );

        if ($db->affected_rows > 0)
          echo "
            jQuery(\"#res1\").show();
          ";
      }
      ?>

      jQuery("textarea").keyup(() => {
        let length = jQuery("textarea").val().length;

        length = maxLength - length;

        jQuery("#chars").text(length);
      });
    });

    function _getDate(par = Date.now()) {
      return new Date(par).toISOString().split("T")[0]
    }

    function _addZero(str) {
      if (str < 10) str = "0" + str;
      return str;
    }
  </script>
</body>

</html>