
<?php

$serverName = "localhost";

/* Get UID and PWD from application-specific files.  */

$uid = "admin";

$pwd = "tjdwls124;

$connectionInfo = array( "UID"=>$uid,

    "PWD"=>$pwd,

    "Database"=>"web",
	"CharacterSet"=>"UTF-8");


/* Connect using SQL Server Authentication. */

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn === false )

{

    echo "Unable to connect.</br>";

    die( print_r( sqlsrv_errors(), true));

}

?> 

