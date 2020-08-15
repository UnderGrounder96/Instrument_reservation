<?php

/**
 *@author  Xu Ding
 *@email   thedilab@gmail.com
 *@website http://www.StarTutorial.com
 *@Source  https://www.startutorial.com/articles/view/how-to-build-a-web-calendar-in-php
Let us take a look at each function in detail.

public function show():This is the only public function Calendar has. This function basically calls each private function below to create the HTML calendar interface.
The basic idea of creating a web calendar is that, firstly it determines how many rows(weeks) to create, and then it loops over the rows and create 7 cells on each row. Meanwhile it puts corresponding day value to the cell according to the day of week (Monday to Sunday).
Take a closer look at each private function below to understand.
private function _showDay():This function will determine what value to put to the created cell. It can be empty or numbers.
private function _createNavi(): This function will create the "Prev" && "Next" navigation buttons on the top of the calendar.
private function _createLabels(): This function will create labels for the day of week. ( Monday to Sunday). You can update the language string to your own choice. But be cautious. You should not change the order of the labels.
private function _weeksInMonth(): This is a tricky function. It can tell you how many weeks are there for a given month. This is used in show() function to create number of rows(weeks).
private function _daysInMonth(); This function tells how many days in a given month.
Functions are working closely to create the PHP calendar. You should follow function show() to understand deeply how exactly they call each. Code is documented. Give yourself some time to read it.
 **/

class Calendar
{

  /********************* PROPERTY ********************/
  private $currentDay = 0;
  private $daysInMonth = 0;
  private $currentYear = 0;
  private $currentMonth = 0;

  private $naviHref = null;
  private $currentDate = null;

  private $dayLabels = array("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");


  /**
   * Constructor
   */
  public function __construct()
  {
    $this->naviHref = htmlentities($_SERVER['PHP_SELF']);
  }


  /********************* PUBLIC **********************/
  /**
   * print out the calendar
   */
  public function show()
  {
    $year = $month = null;

    if (isset($_GET['year']) and isset($_GET['month'])) {
      $year = $_GET['year'];
      $month = $_GET['month'];
    } else {
      $year = date("Y", time());
      $month = date("m", time());
    }

    $this->currentYear = $year;
    $this->currentMonth = $month;
    $this->daysInMonth = $this->_daysInMonth($month, $year);

    $content = '<div id="calendar">' .
      '<div class="box">' .
      $this->_createNavi() .
      '</div>' .
      '<div class="box-content">' .
      '<ul class="label">' . $this->_createLabels() . '</ul>';
    $content .= '<div class="clear"></div>';
    $content .= '<ul class="dates">';

    $weeksInMonth = $this->_weeksInMonth($month, $year);

    // Create weeks in a month
    for ($i = 0; $i < $weeksInMonth; $i++)
      //Create days in a week
      for ($j = 1; $j <= 7; $j++)
        $content .= $this->_showDay($i * 7 + $j);

    $content .= '</ul>';

    $content .= '<div class="clear"></div>';

    $content .= '</div>';

    $content .= '</div>';


    return $content;
  }

  /********************* PRIVATE **********************/
  /**
   * create the li element for ul
   */
  private function _showDay($cellNumber)
  {
    global $db;

    if ($this->currentDay == 0) {
      $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));

      if (intval($cellNumber) == intval($firstDayOfTheWeek))
        $this->currentDay = 1;
    }

    if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
      $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
      $cellContent = $this->currentDay;
      $this->currentDay++;
    } else {
      $this->currentDate = null;
      $cellContent = null;
    }

    if (isset($_SESSION["id_inst"])) {
      $result = $db->query("SELECT power FROM rights WHERE id_user='{$_SESSION['id_user']}' AND id_instrument='{$_SESSION['id_inst']}';");
      $row = $result->fetch_assoc();

      if ($row["power"] > 0) {

        $content = "";

        $result = $db->query("SELECT DISTINCT reservations.date_in, reservations.date_out, reservations.id_user FROM reservations
        JOIN instruments ON reservations.id_instrument=instruments.id_instrument WHERE DATE(reservations.date_in)<='{$this->currentDate}'
        AND DATE(reservations.date_out)>='{$this->currentDate}' AND reservations.id_instrument='{$_SESSION['id_inst']}' AND instruments.active>0;");

        if ($db->affected_rows > 0) {

          while ($row = $result->fetch_assoc()) {
            if ($this->currentDate > date('Y-m-d', strtotime('-1 day', strtotime($row["date_in"]))) && date('Y-m-d', strtotime($row["date_out"])) >= $this->currentDate) {
              if ($_SESSION["id_user"] == $row["id_user"])
                $content .= '<li id="li-' . $this->currentDate . '" class="blue ' . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
                  ($cellContent == null ? 'mask' : '') . '">' . $cellContent . '</li>';

              else
                $content .= '<li id="li-' . $this->currentDate . '" class="red ' . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
                  ($cellContent == null ? 'mask' : '') . '">' . $cellContent . '</li>';
            } else
              $content .= $this->_return($cellNumber, $cellContent);
          }
          return $content;
        } else
          return $this->_return($cellNumber, $cellContent);
      } else
        return $this->_return($cellNumber, $cellContent);
    } else {
      unset($_SESSION["id_inst"]);
      return $this->_return($cellNumber, $cellContent);
    }
  }

  private function _return($cellNumber, $cellContent)
  {
    return '<li id="li-' . $this->currentDate . '" class="' . ($cellNumber % 7 == 1 ? ' start ' : ($cellNumber % 7 == 0 ? ' end ' : ' ')) .
      ($cellContent == null ? 'mask' : '') . '">' . $cellContent . '</li>';
  }


  /**
   * create navigation
   */
  private function _createNavi()
  {

    $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth) + 1;
    $nextYear = $this->currentMonth == 12 ? intval($this->currentYear) + 1 : $this->currentYear;
    $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth) - 1;
    $preYear = $this->currentMonth == 1 ? intval($this->currentYear) - 1 : $this->currentYear;


    return
      '<div class="header">' .
      '<a class="prev" href="' . $this->naviHref . '?month=' . sprintf('%02d', $preMonth) . '&year=' . $preYear . '">Prev</a>' .
      '<span class="title">' . date('M Y', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</span>' .
      '<a class="next" href="' . $this->naviHref . '?month=' . sprintf("%02d", $nextMonth) . '&year=' . $nextYear . '">Next</a>' .
      '</div>';
  }

  /**
   * create calendar week labels
   */
  private function _createLabels()
  {
    $content = '';
    foreach ($this->dayLabels as $index => $label)
      $content .= '<li class="' . ($label == 6 ? 'end title' : 'start title') . ' title">' . $label . '</li>';

    return $content;
  }

  /**
   * calculate number of weeks in a particular month
   */
  private function _weeksInMonth($month = null, $year = null)
  {
    if (null == ($year) && null == ($month)) {
      $year =  date("Y", time());
      $month = date("m", time());
    }

    // find number of days in this month
    $daysInMonths = $this->_daysInMonth($month, $year);
    $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
    $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
    $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));

    if ($monthEndingDay < $monthStartDay)
      $numOfweeks++;

    return $numOfweeks;
  }

  /**
   * calculate number of days in a particular month
   */
  private function _daysInMonth($month = null, $year = null)
  {
    if (null == ($year) && null == ($month)) {
      $year =  date("Y", time());
      $month = date("m", time());
    }

    return date('t', strtotime($year . '-' . $month . '-01'));
  }
}
