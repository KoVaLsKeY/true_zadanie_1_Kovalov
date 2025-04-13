<?php
$host="localhost";
$dbname = "formular";
$port = 3306;
$username = "root";
$password = "";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);
try{
    $conn = new PDO('mysql:host='.$host.';dbname='.$dbname.";port=".$port, $username,
    $password, $options);
} catch(PDOException $e){
    die("Chyba pripojenia: ".$e->getMessage());
}
$meno = $_POST["meno"];
$email = $_POST["email"];
$sprava = $_POST["sprava"];
$objekt = $_POST["objekt"];


$sql = "INSERT INTO udaje (meno, email, sprava, objekt)
VALUE ('$meno', '$email','$sprava', '$objekt')";
$statement = $conn->prepare($sql);
try {
    $insert = $statement->execute();
header("Location:http://localhost/ProjectProject/templatemo_559_zay_shop/vdaka.php");
return $insert;
} catch (Exception $exception){
    return false;
}
$conn = null;