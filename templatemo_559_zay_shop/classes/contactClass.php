<?php
namespace formular;
require_once('../db/dbConfig.php');
use PDO;

class ContactClass{
    private $conn;
    public function __construct(){
        $this -> connect();
    }
private function connect(){
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    );
    try{
        $this -> conn = new PDO('mysql:host='. DATABASE['HOST'] . ';dbname=' . DATABASE['DBNAME'] . ";port=" . DATABASE['PORT'], DATABASE['USER_NAME'],
        DATABASE['PASSWORD'], $options);
    } catch(PDOException $e){
        die("Chyba pripojenia: ".$e->getMessage());
    }
}


public function ulozitSpravu($meno, $email, $sprava, $objekt){
    $sql = "INSERT INTO udaje (meno, email, sprava, objekt)
VALUE ('$meno', '$email','$sprava', '$objekt')";
$statement = $this ->conn->prepare($sql);
try {
    $insert = $statement->execute();
header("Location:http://localhost/ProjectProject/templatemo_559_zay_shop/vdaka.php");
http_response_code(200);
return $insert;
} catch (Exception $exception){
return http_response_code(404);
}
}
public function __destruct(){
    $this->conn = null;
}
}