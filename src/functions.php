<?php
function testInput($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// with DateTime you can make the shortest date&time validator for all formats.
function validateDate($date, $format = 'Y-m-d H:i:s')
{
  $d = DateTime::createFromFormat($format, $date);

  return $d && $d->format($format) == $date;
}

function _output($action)
{
  switch ($action) {
    case "addInst":
      _print("instrument", "added", "admin");
      break;

    case "editInst":
      _print("intrument", "changed", "admin");
      break;

    case "addUser":
      _print("user", "added", "admin");
      break;

    case "editUser":
      _print("user", "changed", "admin");
      break;

    case "addRig":
      _print("right", "given", "admin");
      break;

    case "editRig":
      _print("right", "changed", "admin");
      break;

    case "addRes":
      _print("reservation", "added", "user");;
      break;

    case "editRes":
      _print("reservation", "changed", "user");
      break;

    case "remRes":
      _print("reservation", "deleted", "user");
      break;
  }
}

function _print($par1, $par2, $par3)
{

  if ($par3 == "user")
    echo "<body onLoad=\"loaded()\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:14px;\"><h3><b>The " . $par1 . " data was " . $par2 . " successfully click <a href=\"user.php?inst={$_SESSION['idInst']}\">here</a> to go back...</b></h3></body>";
  else
    echo "<body onLoad=\"loaded()\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:14px;\"><h3><b>The " . $par1 . " data was " . $par2 . " successfully click <a href=\"admin.php\">here</a> to go back...</b></h3></body>";
}

function _check($var)
{
  if (!$var)
    return "no";

  return "yes";
}

function _power($var)
{
  if ($var === "2")
    return "admin";
  else if ($var === "1")
    return "user";

  return "no access";
}

?>
