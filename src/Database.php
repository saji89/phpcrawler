<?php

    class Database
    {

        private $host = DB_HOST;
        private $user = DB_USER;
        private $pass = DB_PASS;
        private $dbname = DB_NAME;

        private $dbConn;
        private $error;
        private $statement;

        public function __construct()
        {
            // Set the data source name
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;

            // Set PDO options
            $pdoOptions = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
            );

            // Create a new PDO instance
            try {
                $this->dbConn = new PDO($dsn, $this->user, $this->pass, $pdoOptions);
            } // Catch any errors
            catch (PDOException $e) {
                $this->error = $e->getMessage();
            }
        }

        /**
         * @param $query
         */
        public function query($query)
        {
            $this->statement = $this->dbConn->prepare($query);
        }

        /**
         * @param      $param
         * @param      $value
         * @param null $type
         */
        public function bind($param, $value, $type = null)
        {
            if (is_null($type)) {
                switch (true) {
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }

            $this->statement->bindValue($param, $value, $type);
        }

        /**
         * @return mixed
         */
        public function execute()
        {
            return $this->statement->execute();
        }

        /**
         * @return mixed
         */
        public function getResultSet()
        {
            $this->execute();

            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * @return mixed
         */
        public function getSingleResult()
        {
            $this->execute();

            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * @return mixed
         */
        public function getRowCount()
        {
            return $this->statement->rowCount();
        }

        /**
         * @return string
         */
        public function getLastInsertId()
        {
            return $this->dbConn->lastInsertId();
        }

        /**
         * @return mixed
         */
        public function debugDumpParams()
        {
            return $this->statement->debugDumpParams();
        }
    }