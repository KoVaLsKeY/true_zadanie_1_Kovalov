<?php
require_once('../classes/contactClass.php');
use formular\ContactClass;


$meno = $_POST['meno'];
$email = $_POST['email'];
$sprava = $_POST['sprava'];
$objekt = $_POST['objekt'];

if(empty($meno) || empty($email)|| empty($sprava) || empty($objekt)){
    die('Chyba: Všetky polia sú povinné!');
}
$kontakt = new ContactClass();
$ulozene = $kontakt->ulozitSpravu($meno, $email, $sprava, $objekt);
if($ulozene){
    header("Location:http://localhost/ProjectProject/templatemo_559_zay_shop/vdaka.php");
}
else{
    die('Chyba pri odoslani spravy do databazy!');
    http_response_code(404);
}

