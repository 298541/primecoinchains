<?php
    //Php MySqlDB-Data
    require_once('lib/conf.php');
    
    //Include header
    include("header.php");
?>

    <p>
        Input block height
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
        
            <input type="number" name="height" min="0">
                <br>
            <input type="submit" value="Get">
        </form>
    </p>
    
    <p>
        Input block hash
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
        
            <input type="text" name="hash" size="90">
                <br>
            <input type="submit" value="Get">
        </form>
    </p>
    <br>
    <br>
    


<?php
    //Get the user input and display prime chain
    getPrimeChain();

    //Include footer
    include("footer.php");
?>











<?php
    function getPrimeChain(){
        
        echo("<p>");
        
        //Connect to DB
        $con = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PW, MYSQL_DB);
        //If connection fails
        if(!$con){
            die("Connection to Database not established");
        }
        
        $sql="
        SELECT `value` FROM `numinos_prime_data` 
        WHERE `info`='blocks';
        ";
        
        //Retrieve block number of most current block
        $primeBlockCount = mysqli_fetch_array(mysqli_query($con, $sql));
        
        $height = NULL;
        $hash = NULL;
        
        //Check for correctness and safeness of user input
        if(isset($_GET["height"])){
            $height = $_GET["height"];
            if($height > $primeBlockCount["value"] || $height < 0 || is_nan(floatval($height)) || $height == NULL){
                echo "404 - Block not found";
                return;
            }
        }
        if(isset($_GET["hash"])){
            $hash = $_GET["hash"];
            if(strlen($hash) != 64 || !ctype_xdigit($hash)){
                echo "404 - Block not found";
                return;
            }else{
                
                $sql="
                SELECT `index` FROM `numinos_prime_blockhashes` 
                 WHERE `hash`='" . $hash . "';
                ";
                
                //Retrieve block number of current hash block
                $primeHashBlock = mysqli_fetch_array(mysqli_query($con, $sql));
                
                //Check if hash exists in the database
                if($primeHashBlock["index"] == NULL){
                    echo "404 - Block not found";
                    return;
                }else{
                    //If it does, set height to hashes height
                    $height = $primeHashBlock["index"];
                }
            }
        }
        
        ////If none of the two variables are set - return nothing
        if(isset($_GET["height"]) == FALSE && isset($_GET["hash"]) == FALSE){
            //Set height to latest block to show
            $height = $primeBlockCount["value"] - 1;
        }
        
        //If database is reachable and height is not null
        if($con && isset($height)){
            //If primechainerPath points to correct exe
            if(file_exists(PRIMECHAINERPATH)){
                
                //Get primeorigin, primechain and length for given height
                $sql = "
                SELECT `hash`, `primeorigin`, `primechain`, `length` FROM `numinos_prime_blockhashes`
                WHERE `index`='" . $height . "';
                ";
                
                //Get prime origin and prime chain type to parse to external primechain calculator primechainer.exe
                $primeDataArray = mysqli_fetch_array(mysqli_query($con, $sql));
                $primeChainType = explode(".", $primeDataArray["primechain"]);
            
                //array_reverse because primechains are in reverse order
                $primeChainArray = array_reverse(explode(",", shell_exec(PRIMECHAINERPATH . " " . $primeDataArray["primeorigin"] . " " . $primeChainType[0] . " " . $primeDataArray["length"])));
                
                //Table to wrap navigation buttons and height/hash table
                echo("<table border=\"0\"><tr>");
                
                //Previous Block button
                echo("
                <td><form action=\"" . htmlentities($_SERVER['PHP_SELF']) . "\" method=\"get\">
    
                <input type=\"hidden\" name=\"height\" value=\"" . ($height - 1) . "\">
                <br>
                <input type=\"submit\" value=\"Previous\">
                  
                </select>
                </form></td>
                ");
                
                //Chain height/hash table
                echo("<td><table border=\"1\">
                <tr>
                  <th>Height</th>
                  <th>Hash</th>
                </tr>
                <tr>
                  <td style=\"text-align:center;\">" . $height . "</td>
                  <td>" . $primeDataArray["hash"] . "</td>
                </tr>
                </table></td>
                </p>");
                
                //Next Block button
                echo("
                <td><form action=\"" . htmlentities($_SERVER['PHP_SELF']) . "\" method=\"get\">
    
                <input type=\"hidden\" name=\"height\" value=\"" . ($height + 1) . "\">
                <br>
                <input type=\"submit\" value=\"    Next    \">
                  
                </select>
                </form></td>
                ");
                
                //END Table to wrap navigation buttons and height/hash table
                echo("</tr></table>");
                
                //Chain info table
                echo("<p>
                <table border=\"1\">
                <tr>
                  <th>Origin</th>
                  <th>Type</th>
                  <th>Length</th>
                </tr>
                <tr>
                  <td>" . $primeDataArray["primeorigin"] . "</td>
                  <td style=\"text-align:center;\">" . substr($primeChainType[0], 0 , 3) . "</td>
                  <td style=\"text-align:center;\">" . $primeDataArray["length"] . "</td>
                </tr>
                </table>
                </p>");
                
                //Chain data table
                echo("<p>
                <table border=\"1\">
                <tr>
                  <th>Chain</th>
                </tr>");
                
                foreach(array_filter($primeChainArray) as $primeChainPiece){
                    echo("<tr><td>" . $primeChainPiece . "</td></tr>");
                }
                
                echo("</table>");
                
                echo("</p>");
            }
        } 
    }
?>
