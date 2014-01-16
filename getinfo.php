<?php

/**
 *    File: getinfo.php
 *    Name: lacksfish
 *    Date: 09/06/13
 * Project: Primecoinchains
 *    Desc: This script is part of a cronjob executed every minute to determine the total
 *          blockcount present in the Primecoin network. This information is aquired by
 *          using the primecoind.exe --daemon and then stored in a MySQL Database with proper
 *          timestamp. Every information of "getinfo" is stored there.
 */

require_once('lib/conf.php');

//Connect to MySQL database
$con = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PW, MYSQL_DB);
        //If connection fails
        if(!$con){
            die("Connection to Database not established");
        }

if($con){
    
    //If primecoindPath points to primecoind.exe
    if(file_exists($primecoindPath)){
        echo "wooow";
        //Get full network information           
        $primeGetInfo = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getinfo");
	echo(shell_exec('ls /tmp 2>&1'));
 
        //Split into separate information strings
        $primeGetInfoArray = array();
        $primeGetInfoArray = explode(",", $primeGetInfo);
        
        //Check if table numinos_prime_data does not yet exists
        if(mysqli_query($con, "SHOW TABLES LIKE 'numinos_prime_data';")->num_rows == NULL){
                        
            //Create it
            $sql = "
            CREATE TABLE IF NOT EXISTS numinos_prime_data
            (
                info TEXT,
                value TEXT,
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
            ";
            $res = mysqli_query($con, $sql) 
                or die("Tabelle wurde nicht geschrieben: " . mysqli_error($con));
        }
        
        //updateFlag will be set TRUE if there are rows in the table
        $updateFlag = FALSE;
        if(mysqli_query($con, "SELECT * FROM numinos_prime_data")->num_rows != NULL){
            $updateFlag = TRUE;
        }
        
        //For each information string, check if it cointains total block info
        foreach($primeGetInfoArray as $primeGetInfoString){
            
            //Take appart the "xxxxxxx : xxxxxxx" String at the colon
            $primeGetInfoStringArray = explode(" : ", $primeGetInfoString);
            //Remove '{', ' ', '"' and '}'
            $needles = array("{", "}", "\"", " ");
            $primeGetInfoStringArray = str_replace($needles, "", $primeGetInfoStringArray);
            //Remove all tabs, newlines and carriage returns
            $primeGetInfoStringArray = preg_replace("/\r|\n|\t/", "", $primeGetInfoStringArray);
            
            
            //If there are no rows within the table
            if($updateFlag == FALSE){
                //Insert is needed
                $sql = "
                INSERT INTO `numinos_prime_data`
                (
                    `info` , `value`
                )
                VALUES
                (
                    '" . $primeGetInfoStringArray[0] . "' , '" . $primeGetInfoStringArray[1] . "'
                );
                ";
            }else{
                //Update is needed
                $sql = "
                UPDATE `numinos_prime_data`
                SET `info`='" . $primeGetInfoStringArray[0] . "', `value`='" . $primeGetInfoStringArray[1] . "', `time`=CURRENT_TIMESTAMP
                WHERE `info`='" . $primeGetInfoStringArray[0] . "';
                ";
            }//else
            
            $res = mysqli_query($con, $sql) 
                or die("Datensatz wurde nicht geschrieben: " . mysqli_error($con));
                
        }//foreach
    }//if
    
    //Close MySQL Connection
    mysqli_close($con);
    
}//if($con)
?>
