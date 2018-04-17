<?php
require_once ('config.php');
class Database
{    
    public $conn;


    public function dbConnection()
	{
        $this->conn = null;
        $config = new Config();
        try
		{
            $this->conn = new PDO("mysql:host=" .$config->get_host() . ";dbname=" . $config->get_db_name(), $config->get_db_username(), $config->get_db_password());
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
		catch(PDOException $exception)
		{
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
