<?php
// we will be sending Javascript codes, remember...
header('Content-Type: text/javascript'); 

// select the timezone for your countdown
$timezone = trim($_GET['timezone']);
putenv("TZ=$timezone");

// Counting down to New Year's on 2020
$countdown_to = trim($_GET['countto']); // 24-Hour Format: YYYY-MM-DD HH:MM:SS"

// Getting the current time
$count_from = date("Y-m-d H:i:s"); // current time -- NO NEED TO CHANGE

// Date difference function. Will be using below
function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
  /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
      (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
  */
  
  if (!$using_timestamps) {
    $datefrom = strtotime($datefrom, 0);
    $dateto = strtotime($dateto, 0);
  }
  $difference = $dateto - $datefrom; // Difference in seconds
   
  switch($interval) {
   
    case 'yyyy': // Number of full years

      $years_difference = floor($difference / 31536000);
      if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
        $years_difference--;
      }
      if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
        $years_difference++;
      }
      $datediff = $years_difference;
      break;

    case "q": // Number of full quarters

      $quarters_difference = floor($difference / 8035200);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $quarters_difference--;
      $datediff = $quarters_difference;
      break;

    case "m": // Number of full months

      $months_difference = floor($difference / 2678400);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $months_difference--;
      $datediff = $months_difference;
      break;

    case 'y': // Difference between day numbers

      $datediff = date("z", $dateto) - date("z", $datefrom);
      break;

    case "d": // Number of full days

      $datediff = floor($difference / 86400);
      break;

    case "w": // Number of full weekdays

      $days_difference = floor($difference / 86400);
      $weeks_difference = floor($days_difference / 7); // Complete weeks
      $first_day = date("w", $datefrom);
      $days_remainder = floor($days_difference % 7);
      $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
      if ($odd_days > 7) { // Sunday
        $days_remainder--;
      }
      if ($odd_days > 6) { // Saturday
        $days_remainder--;
      }
      $datediff = ($weeks_difference * 5) + $days_remainder;
      break;

    case "ww": // Number of full weeks

      $datediff = floor($difference / 604800);
      break;

    case "h": // Number of full hours

      $datediff = floor($difference / 3600);
      break;

    case "n": // Number of full minutes

      $datediff = floor($difference / 60);
      break;

    default: // Number of full seconds (default)

      $datediff = $difference;
      break;
  }    

  return $datediff;
}

// getting Date difference in SECONDS
$diff = datediff("s", $count_from, $countdown_to);
?>

// Here’s where the Javascript starts
countdown = <?=$diff?>;

// Converting date difference from seconds to actual time
function convert_to_time(secs)
{
	secs = parseInt(secs);	
	hh = secs / 3600;	
	hh = parseInt(hh);	
	mmt = secs - (hh * 3600);	
	mm = mmt / 60;	
	mm = parseInt(mm);	
	ss = mmt - (mm * 60);	
		
	if (hh > 23)	
	{	
	   dd = hh / 24;	
	   dd = parseInt(dd);	
	   hh = hh - (dd * 24);	
	} else { dd = 0; }	
		
	if (ss < 10) { ss = "0"+ss; }	
	if (mm < 10) { mm = "0"+mm; }	
	if (hh < 10) { hh = "0"+hh; }	
	if (dd == 0) { return (hh+":"+mm+":"+ss); }	
	else {	
		if (dd > 1) { return (dd+" дней "+hh+":"+mm+":"+ss); }
		else { return (dd+" день "+hh+":"+mm+":"+ss); }
	}	
}

// Our function that will do the actual countdown
function do_cd()
{
	if (countdown < 0)	
	{ 	
		<?php
			if(strtolower(trim($_GET['do'])) == 'r' )
			{
		?>
		// redirect web page
		document.location.href = "<?=$_GET['data']?>";
		<?php } ?>

		<?php
			if(strtolower(trim($_GET['do'])) == 't' )
			{
		?>
		// change text
		document.getElementById('cd').innerHTML = "<?=$_GET['data']?>";
		<?php } ?>

	}	
	else	
	{	
		document.getElementById('cd').innerHTML = convert_to_time(countdown);
		setTimeout('do_cd()', 1000);
	}	
	countdown = countdown - 1;	
}

document.write("<span id='cd'></span>\n");

do_cd();

<? exit(); ?>
