<?php
/**
 * This is the Database connection class
 * 
 * @category  Database
 * @package   Samz851\USER
 * @author    Samer Alotaibi <sam.otb@hotmail.ca>
 * @copyright 2018 Samer Alotaibi
 */
require_once 'config.php';

/**
 * Class Database
 * 
 * @category Class
 * @package  Samz851\USER
 * @author   Samer Alotaibi <sam.otb@hotmail.ca>
 */
class Database
{
    public $conn;

    /**
     * Database connection
     * Creates a PDO instance representing the database connection
     *
     * @return PDO|PDOException  
     */
    public function dbConnection()
    {
        $this->conn = null;
        $config = new Config();
        try
        {
            $this->conn = new PDO("mysql:host=" .$config->getHost() . ";dbname=" . $config->getDBName(), $config->getDBUsername(), $config->getDBPassword());
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
