

<?php

include('includes/connect.php');

function createUrl($ticker)
{
	$currentMonth = date("n");
	$currentMonth = $currentMonth - 1;
	$currentDay = date("j");
	$currentYear = date("Y");
	
	
	return "http://real-chart.finance.yahoo.com/table.csv?s=$ticker&d=$currentMonth&e=$currentDay&f=$currentYear&g=d&a=11&b=12&c=2014&ignore=.csv";
}

function getCsvFile($url,$outputFile)
{	
	$content = file_get_contents($url);
	$content = str_replace("Date,Open,High,Low,Close,Volume,Adj Close","",$content); //Replaces String by white space
	$content = trim($content);  //Removes White Space
	file_put_contents($outputFile,$content);
}

function fileToDataBase($txtFile,$tableName)
{
	$file = fopen($txtFile,"r");
	while(!feof($file))//Loops until get to end of the File
	{
		$line = fgets($file);
		$pieces = explode(",",$line);//Picks off a string by a separator provided , (Takes in 2 Parameters)
		
		$date = $pieces[0];
		$open = $pieces[1];
		$high = $pieces[2];
		$low = $pieces[3];
		$close = $pieces[4];
		$volume = $pieces[5];
		$amount_cgange = $close - $open;   //Gets one days Change in Price either - or +
		$percent_change = ($amount_cgange/$open )*100; //Takes amount changed converts to % shows how much it moved up or down relative to its price
		
		$sql = "SELECT *FROM $tableName";
		$result = mysql_query($sql);
		
		if(!$result)
		{
			$sql2 = "CREATE TABLE $tableName (date DATE, PRIMARY KEY(date), open FLOAT, high FLOAT, low FLOAT, close FLOAT, volume INT, amount_cgange FLOAT, percent_change FLOAT )";
			mysql_query($sql2);
		}
		
		$sql3 = "INSERT INTO $tableName (date, open, high, low, close, volume, amount_cgange, percent_change) VALUES ('$date','$open','$high','$low','$close','$volume','$amount_cgange','$percent_change')";
		mysql_query($sql3);
			
	}
	fclose($file);
}

function main()
{
	$mainTickerFile = fopen("tickerMaster.txt","r");
	while(!feof($mainTickerFile))
	{
		$companyTicker = fgets($mainTickerFile);
		$companyTicker = trim($companyTicker);
		
		$fileURL = createUrl($companyTicker);
		$companyTxtFile = "txtFiles/".$companyTicker."txt";
		getCsvFile($fileURL,$companyTxtFile );
		fileToDataBase($companyTxtFile,$companyTicker);
	}

}
main();
echo " It Works <br />";
?>
