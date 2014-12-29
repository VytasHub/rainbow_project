<?php

include('../includes/connect.php');

function masterLoop()
{
	$mainTickerFile = fopen("../tickerMaster.txt","r");
	while(!feof($mainTickerFile)) //feof a pointer checking for end of the file loops throw all files
	{
		$compTicker = fgets($mainTickerFile);
		$compTicker  = trim($compTicker);
		
		$nextDayIncrease = 0;
		$nextDayDecrease = 0;
		$nextDayNoChange = 0;
		$total = 0;
		
		$sumOfIncreases = 0;
		$sumOfDecreases = 0;
		
		$sql = "SELECT date ,percent_change FROM $compTicker WHERE percent_change < '0' ORDER BY DATE ASC ";
		//SQL statement gets all the days where stock went down for one day
		$result = mysql_query($sql);
		
		if($result)
		{
			while($row = mysql_fetch_array($result))//This while loop will throw all the Rows
			{
				$date = $row['date'];
				$percent_change = $row['percent_change'];
				$sql2 = "SELECT date, percent_change FROM $compTicker WHERE date > '$date' ORDER BY date ASC LIMIT 1 "; 
				$result2 = mysql_query($sql2);
				$numberOfRows = mysql_num_rows($result2);
				
				if($numberOfRows == 1)
				{
					$row2 = mysql_fetch_row($result2);
					$tomorow_date = $row2[0];
					$tom_percent_change = $row2[1];
					
					if($tom_percent_change > 0)
					{
						$nextDayIncrease++;
						$sumOfIncreases +=$tom_percent_change;
						$total++;
					}
					else if($tom_percent_change < 0)
					{
						$nextDayIncrease++;
						$sumOfDecreases +=$tom_percent_change;
						$total++;
					}
					else
					{
						$nextDayNoChange++;
						$total++;
					}
				}
			}
		}
		else
		{
			echo "Unable To select Table $compTicker <br />";
		}
		
		$nextDayIncreassePercent = ($nextDayIncrease/$total) * 100;
		$nextDayDecreassePercent = ($nextDayDecrease/$total) * 100;
		$averageIncreassePercent = $sumOfIncreases/$nextDayIncrease;
		$averageDecreassePercent = $sumOfDecreases/$nextDayIncrease;
		
		insertIntoResultTable();
	}

}

function insertIntoResultTable($compTicker,$nextDayIncrease,$nextDayIncreassePercent,$averageIncreassePercent,$nextDayIncrease,$nextDayDecreassePercent,$averageDecreassePercent)
{
	$vytasBuyValue = $nextDayIncreassePercent * $averageIncreassePercent;
	$vytasSellValue = $averageDecreassePercent * $averageDecreassePercent;
	
	$query = "SELECT * FROM analysisA WHERE ticker='$compTicker' ";
	$result = mysql_query($query);
	$numberOfRows = mysql_num_rows($result);
	
	if($numberOfRows==1)
	{
		$sql = "UPDATE analysisA SET ticker ='$compTicker', daysInc ='$nextDayIncrease',pctDaysInc='$nextDayIncreassePercent' , avgIncPct='$averageIncreassePercent' , daysDec='$nextDayDecrease' , pctOfDaysDec='$nextDayDecreassePercent' ,avgDecPct='$averageDecreassePercent' ,vytasBuyValue='$vytasBuyValue' , vytasSellValue='$vytasSellValue' WHERE ticker='$compTicker' ";                                                      
		mysql_query($sql);
	}
	else
	{
		$sql = "INSERT INTO analysisA (ticker,daysInc,pctDaysInc,avgIncPct,daysDec,pctOfDaysDec,avgDecPct,vytasBuyValue,vytasSellValue) VALUES ('$compTicker','$nextDayIncrease','$nextDayIncreassePercent','$averageIncreassePercent','$nextDayDecrease','$nextDayDecreassePercent','$averageDecreassePercent','$vytasBuyValue','$vytasSellValue') ";
		mysql_query($sql);
	}
}

masterLoop();





?>