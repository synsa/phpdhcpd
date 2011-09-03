<?php


function parser($open_file) 
{
	$line_number = 0;
	$row_array = array(array());
	while (!feof($open_file))
	{	
		$read_line = fgets($open_file, 4096);
		if (substr($read_line, 0, 1) != "#") //check for comment (skip)
		{
			$tok = strtok($read_line, " ");
			if ($tok == "lease")
			{
				$row_array[$line_number] = initialize_array();
				$row_array[$line_number][0] = strtok(" ")."\n";
			}
			else if ($tok == "starts")
			{
				$row_array[$line_number][1] =   intToDay(strtok(" "));
				$row_array[$line_number][1] = $row_array[$line_number][1]." - " . strtok(" ") . " ";
				$time = strtok(" ");
				$time = str_replace(";", "", $time);
				$row_array[$line_number][1] = $row_array[$line_number][1].$time ;
			}
			else if ($tok == "ends")
			{
				$row_array[$line_number][2] = intToDay(strtok(" "));
				$row_array[$line_number][2] = $row_array[$line_number][2]." - " . strtok(" ") . " ";
				$time = strtok(" ");
				$time = str_replace(";", "", $time);
				$row_array[$line_number][2] = $row_array[$line_number][2].$time ;
			}	
			else if ($tok == "tstp")
			{
				$row_array[$line_number][3] = intToDay(strtok(" "));
				$row_array[$line_number][3] = $row_array[$line_number][3]." - " . strtok(" ") . " ";
				$time = strtok(" ");
				$time = str_replace(";", "", $time);
				$row_array[$line_number][3] = $row_array[$line_number][3].$time ;
			}
			else if ($tok == "hardware")
			{
				$row_array[$line_number][4] = strtok(" ") . " - ";
				$MAC = strtok(" ");
				$MAC = strtoupper(str_replace(";", "", $MAC));
				$MAC = strtoupper(str_replace("ethernet - ", "", $MAC));
				
				$row_array[$line_number][4] = $MAC." (".getmacvendor($MAC).")";
			}
			else if ($tok == "uid")
			{
				$uid = strtok(" ");
				$replace = array(".", "\"", ";");
				$uid = str_replace($replace, "", $uid);
				$row_array[$line_number][5] = $uid ;
			}
			else if ($tok == "client-hostname")
			{
				$hostname = strtok(" ");
				$replace = array("\"", ";");
				$hostname = str_replace($replace, "", $hostname);
				$row_array[$line_number][6] = $hostname ;
			}
			else if ($tok == "}\n")
			{
				$row_array[$line_number][6] = $row_array[$line_number][6];
				$line_number++;
				
			}
		}
	}
	
	return $row_array;
}


function intToDay($integer)
{
	if ($integer == 0)
	return "Sunday";
	else if ($integer == 1)
	return "Monday";
	else if ($integer == 2)
	return "Tuesday";
	else if ($integer == 3)
	return "Wednesday";
	else if ($integer == 4)
	return "Thursday";
	else if ($integer == 5)
	return "Friday";
	else
	return "Saturday";
}

function initialize_array()
{
	$row_array = array();
	for ($i = 0; $i < 7; $i++) {
		$row_array[$i] = "-";
	}
	return $row_array;
}

function print_line($row, $css_num)
{
	for ($i = 0; $i < 7; $i++) {
		switch ($i) {
		case 0: 
			//IP Address
			echo "<tr class='row".$css_num."'><td>" . $row[0]. "</td>\n"; 
			break;
		case 1: 
			//Start Time
			echo "<td>".$row[1]. "</td>\n"; 
			break;
		case 2: 
			//End Time
			echo "<td>".$row[2]. "</td>\n"; 
			break;
		case 3: 
			//Lease Expires
			echo "<td>".$row[3]."</td>\n"; 
			break;
		case 4: 
			//MAC Address
			echo "<td>".$row[4]."</td>\n"; 
			break;
		case 5: 
			//Client Identifier
			echo "<td>".$row[5]. "</td>\n"; 
			break;
		case 6:
			//Hostname
			echo "<td>".$row[6]. "</td>\n</tr>";
			break;
		}
	}
}

function compare_ip($a, $b) 
{
	return strnatcmp($a[0], $b[0]);
}

function compare_start_time($a, $b) 
{
	return strnatcmp($a[1], $b[1]);
}

function compare_end_time($a, $b) 
{
	return strnatcmp($a[2], $b[2]);
}

function compare_lease_expire($a, $b) 
{
	return strnatcmp($a[3], $b[3]);
}

function compare_mac($a, $b)
{ 
	return strnatcmp($a[4], $b[4]);
}

function compare_uid($a, $b)
{
	return strnatcmp($a[5], $b[5]);
}

function compare_hostname($a, $b)
{
	return strnatcmp($a[6], $b[6]);
}

function getmacvendor($mac_unformated)
{
	//Can be retrived on nmap http://nmap.org/book/nmap-mac-prefixes.html 
	//or via http://standards.ieee.org/develop/regauth/oui/oui.txt
	//Location of the mac vendor list file
	$mac_vendor_file = "./nmap-mac-prefixes";

	$mac = substr(strtoupper(str_replace(array(":"," ","-"), "", $mac_unformated)),0,6);

	$open_file = fopen($mac_vendor_file, "r") or die("Unable to open MAC VENDOR file.");
	if ($open_file)
	{
		while (!feof($open_file))
		{
			 $read_line = fgets($open_file, 4096);
			 if (substr($read_line, 0, 6) == $mac) {
				return substr($read_line, 7, -1);
			 }
		}
		
		fclose($open_file);
	}
	return "Unknown device";
}


function print_table($dhcptable, $searchfilter, $sort_column)
{
	$order = 0;
	switch ($sort_column) {
		case 1: 
			usort($dhcptable, 'compare_ip');
			break;
		case 2: 
			usort($dhcptable, 'compare_start_time');
			break;
		case 3: 
			usort($dhcptable, 'compare_end_time'); 
			break;
		case 4: 
			usort($dhcptable, 'compare_lease_expire'); 
			break;
		case 5: 
			usort($dhcptable, 'compare_mac');
			break;
		case 6: 
			usort($dhcptable, 'compare_uid');
			break;
		case 7:
			usort($dhcptable, 'compare_hostname');
			break;
		case -1: 
			usort($dhcptable, 'compare_ip');
			$order=-1;
			break;
		case -2: 
			usort($dhcptable, 'compare_start_time');
			$order=-1;
			break;
		case -3: 
			usort($dhcptable, 'compare_end_time'); 
			$order=-1;
			break;
		case -4: 
			usort($dhcptable, 'compare_lease_expire'); 
			$order=-1;
			break;
		case -5: 
			usort($dhcptable, 'compare_mac');
			$order=-1;
			break;
		case -6: 
			usort($dhcptable, 'compare_uid');
			$order=-1;
			break;
		case -7:
			usort($dhcptable, 'compare_hostname');
			$order=-1;
			break;
	}
	
	
	$displayed_line_number = 0;
	if ($order >= 0) {
		//Read every line of the table
		for ($line = 0; $line < count($dhcptable); $line++){
			//Check if the line contains the searched request
			if ($searchfilter != "")
			{
				$displayline = 0;
				for ($i = 0; $i < 7; $i++){
					if (stristr (strtolower($dhcptable[$line][$i]),strtolower($searchfilter))== TRUE) {
						$displayline = 1;
					}
				}

				if ($displayline == 1) {
					$css_num = $displayed_line_number % 2;
					print_line($dhcptable[$line], $css_num);
					$displayed_line_number++;
				}
			}
			else
			{
				$css_num = $displayed_line_number % 2;
				print_line($dhcptable[$line], $css_num);
				$displayed_line_number++;
			}
		}
	}
	else
	{
		for ($line = count($dhcptable)-1; $line >= 0; $line--){
			//Check if the line contains the searched request
			if ($searchfilter != "")
			{
				$displayline = 0;
				for ($i = 0; $i < 7; $i++){
					if(stristr (strtolower($dhcptable[$line][$i]), strtolower($searchfilter))== TRUE) {
						$displayline = 1;
					}
				}

				if ($displayline == 1) {
					$css_num = $displayed_line_number % 2;
					print_line($dhcptable[$line], $css_num);
					$displayed_line_number++;
				}
			}
			else
			{
				$css_num = $displayed_line_number % 2;
				print_line($dhcptable[$line], $css_num);
				$displayed_line_number++;
			}
		}
	}
}
?>