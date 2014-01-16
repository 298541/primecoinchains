<?php

/**
 *    File: getblocks.php
 *    Name: lacksfish
 *    Date: 09/06/13
 * Project: Primecoinchains
 *    Desc: This script will extract the index, hash, primechain type, prime origin, length and difficulty
 *          of every block within the networks blockchain. 
 *          It has to be run, just like getinfo.php, via a cronjob every minute.
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
        
        //Check if table numinos_prime_data does not yet exists
        if(mysqli_query($con, "SHOW TABLES LIKE 'numinos_prime_blockhashes';")->num_rows == NULL){
                        
            //Create it
            $sql = "
            CREATE TABLE IF NOT EXISTS numinos_prime_blockhashes
            (
                `index` INT,
                `hash` TEXT,
                `primechain` TEXT,
                `primeorigin` TEXT,
                `length` INT,
                `difficulty` DOUBLE
            );
            ";
            $res = mysqli_query($con, $sql) 
                or die("Tabelle wurde nicht geschrieben: " . mysqli_error($con));
        }
        
        $sql="
        SELECT `value` FROM `numinos_prime_data` 
        WHERE `info`='blocks';
        ";
        
        //Retrieve block number of most current block
        $primeBlockCount = mysqli_fetch_array(mysqli_query($con, $sql));
        
        $formatedBlockArray = array();
        
        //Get latest block processed (highest id)
        $sql = "
        SELECT `index` FROM `numinos_prime_blockhashes`
        ORDER BY `index` DESC LIMIT 0, 1;
        ";
        $maxDbBlockID = mysqli_fetch_array(mysqli_query($con, $sql));
        
        //If there are no entries in the database
        if(is_null($maxDbBlockID["index"])){
            //Set $maxDbBlockID to zero
            $maxDbBlockID["index"] = 0;
        }else{//If there are already entries in the database
            //Get next block data
            $lastBlockhash = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getblockhash " . $maxDbBlockID["index"]);
            $lastBlock = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getblock " . $lastBlockhash);
            //Format block
            $formatedBlockArray = getFormatedBlock($lastBlock);
        }
        
        //Process all them blocks
        for($i = $maxDbBlockID["index"]; $i <= $primeBlockCount["value"]; $i++){
            
            //If it is not the genesis block
            if($i != 0){
                
                //Get next block based on nextblockhash
                $Block = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getblock " . $formatedBlockArray["nextblockhash"]);
                
                //Format block
                $formatedBlockArray = getFormatedBlock($Block);
                
                $sql = "
                INSERT INTO `numinos_prime_blockhashes`
                (
                    `index` , `hash` , `primechain` , `primeorigin` , `length` , `difficulty`
                )
                VALUES
                (
                    '" . $formatedBlockArray["height"] . "' , '" . $formatedBlockArray["hash"] . "' , '" . $formatedBlockArray["primechain"] . "' , '" . $formatedBlockArray["primeorigin"] . "' , '" . getLength($formatedBlockArray["primechain"]) . "' , '" . $formatedBlockArray["difficulty"] . "'
                );
                ";

            }else{//If it is the genesis block
                
                //Get genesis block data
                $genesisBlockhash = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getblockhash 0");
                $genesisBlock = shell_exec($primecoindPath . " -datadir=" . $primecoindDataDir . " getblock " . $genesisBlockhash);
                
                //Format genesis block data
                $formatedBlockArray = getFormatedBlock($genesisBlock);
                
                $sql = "
                INSERT INTO `numinos_prime_blockhashes`
                (
                    `index` , `hash` , `primechain` , `primeorigin` , `length` , `difficulty`
                )
                VALUES
                (
                    '" . $formatedBlockArray["height"] . "' , '" . $formatedBlockArray["hash"] . "' , '" . $formatedBlockArray["primechain"] . "' , '" . $formatedBlockArray["primeorigin"] . "' , '" . getLength($formatedBlockArray["primechain"]) . "' , '" . $formatedBlockArray["difficulty"] . "'
                );
                ";                
            }
            
            //Execute query
            $res = mysqli_query($con, $sql) 
                or die("Datensatz wurde nicht geschrieben: " . mysqli_error($con));
        }
        
        //Remove broken entrys
        $sql="
        DELETE FROM `numinos_prime_blockhashes`
        WHERE `difficulty`=0;
        ";
        //Execute query
        $res = mysqli_query($con, $sql) 
            or die("Datensatz wurde nicht geschrieben: " . mysqli_error($con));
        
    }//if
    //Close MySQL Connection
    mysqli_close($con);
    
}//if($con)

function getFormatedBlock($Block){

    //I have to admit, i didn't know what JSON was by the time I programmed this.. Wish I did.
    
    $formatedBlockArray = array();
    
    //tx contains a ",", so the following explode would mess it up
    //replacing tx's "," with a ";"
    $Block = str_replace("\",\n        \"", "\";\n        \"", $Block);
    //Split into separate information strings
    $BlockArray = explode(",", $Block);
    
    foreach($BlockArray as $BlockString){
        
        //Take appart the "xxxxxxx : xxxxxxx" String at the colon
        $BlockStringArray = explode(" : ", $BlockString);
        //Remove '{', ' ', '"' and '}'
        $needles = array("{", "}", "\"", " ", "[", "]");
        $BlockStringArray = str_replace($needles, "", $BlockStringArray);
        //Remove all tabs, newlines and carriage returns
        $BlockStringArray = preg_replace("/\r|\n|\t/", "", $BlockStringArray);
        
        //Add block data to $formatedBlockArray
        $formatedBlockArray[$BlockStringArray[0]] = $BlockStringArray[1];
    }
    
    return $formatedBlockArray;
}

function getLength($primechaintype){
    
    //Strip length in hex format from $primechaintype (1CC07 would be 7, TWN0a would be 10)
    $chainlengthhex = substr($primechaintype, 3, 2);
    //Convert hex to dec and return
    return base_convert($chainlengthhex, 16, 10);
}

?>
