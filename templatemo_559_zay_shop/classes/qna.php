<?php 
namespace otazkyOdpovede;
define('__ROOT', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/db/dbConfig.php');
use PDO;

class QnA{
    private $conn;
    public function __construct(){
        $this->connect();
    }
    private function connect(){
        $config = DATABASE;

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );
        try{
            $this->conn =  new PDO('mysql:host='. DATABASE['HOST'] . ';dbname=' . DATABASE['DBNAME'] . ";port=" . DATABASE['PORT'], DATABASE['USER_NAME'],
            DATABASE['PASSWORD'], $options);
        } catch(PDOException $e){
            die("Chyba pripojenia: ".$e->getMessage());
        }
    }
    public function insertQnA(){
        try{
            $data = json_decode(file_get_contents(__ROOT__. '/data/otazky.json', true));
            $otazky = $data["otazky"];
            $odpovede = $data["odpovede"];

            $this->conn->beginTransaction();
            $sql = "INSERT INTO qna (otazky, odpovede) VALUES(:otazky, :odpovede)";
            $statement = $this->conn->prepare($sql);
            for($i = 0; $i<count($otazky);$i++){
                $statement->bindParam(':otazky', $otazky[$i]);
                $statement->bindParam(':odpovede', $odpovede[$i]);
                $statement->execute();
            }
            $this->conn->commit();
            echo "data boli vlozene";
        } catch (Exception $e){
            echo "chyba pri vkladani dat do databazy: " . $e->getMessage();
            $this->conn->rollback();
        }
        finally{
            $this->conn = null;
        }
    }
}
