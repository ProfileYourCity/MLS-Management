<?php

class MLSimgModel 
{
        public $db_host;
    
        public $db_name;
    
        public $db_user;
    
        public $db_pass;
    
        public $db_table;
        
        /**
         * The usual constructor doing what they do. 
         */
        function __construct($db_host = 'localhost', $db_user = NULL, $db_pass = NULL, $db_name = NULL, $db_table = NULL) 
        {
                // Get DB settings.
                $this->db_host  = $db_host;
                $this->db_user  = $db_user;
                $this->db_pass  = $db_pass;
                $this->db_name  = $db_name;
                $this->db_table = $db_table;
        }
        
        /**
         * Create and return database handle.
         *  
         */
        public function connect()
        {
            try {
                    // MySQL with PDO_MYSQL
                    $DBH = new PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user, $this->db_pass);
                    if ( !empty($DBH) ) {
                        $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // TODO Move this to DB config files.
                        return $DBH;
                    } else {
                        return FALSE;
                    }
            }
            catch(PDOException $e) {
                    // TODO Improve error handling.
                    echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
        
        /**
         * Get all mls_listing_id numbers from given database.
         * 
         * @return mixed  
         */
        function getAllMlsListingIds()
        {
                $mls_listing_ids = array();
            
                try {
                    
                        $DBH = $this->connect(); 
                        if ( $DBH == FALSE ) {
                            $error = 'mlsImgModel: Failed to connect to database.';
                            throw new Exception($error);
                            return FALSE;
                        } else {
                            $STH = $DBH->prepare("SELECT mls_listing_id FROM {$this->db_table}");
                            $STH->execute();
                            $results = $STH->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($results as $result) {
                                $mls_listing_ids[] = $result['mls_listing_id'];
                            }
                            return $mls_listing_ids;
                        }
                        
                }
                
                catch (Exception $e) {
                    
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                        
                }
        }         
        
        /**
         * Gets mls_listing_id numbers for specific community(ies).
         * 
         * @param mixed $communities
         * 
         * @return mixed 
         */
        function getMlsListingIdsByCommunity($communities)
        {
                $mls_listing_ids = array();
            
                try {
                    
                        $DBH = $this->connect(); 
                        if ( $DBH == FALSE ) {
                            
                            $method = __METHOD__;
                            $error = "{$method} Failed to connect to database.";
                            throw new Exception($error);
                            return FALSE;
                            
                        } else {
                            
                            if ( is_string($communities) ) {

                                $community = $communities; // Just correcting semantics of variable name.
                                $STH = $DBH->prepare("SELECT mls_listing_id FROM {$this->db_table} WHERE community_name = {$community}");

                            } elseif ( is_array($communities) ) {
                                $first_community = array_shift($communities);
                                $SQL = "SELECT mls_listing_id FROM {$this->db_table} WHERE community_name = {$first_community} ";
                                foreach ($communities as $community) {
                                    $SQL .= "OR WHERE community_name = {$community} ";
                                }
                                $STH = $DBH->prepare($SQL);

                            } else {

                                $method = __METHOD__;
                                echo "Unsupported datatype passed into {$method}.";
                                die();

                            }
                            $STH->execute();
                            $results = $STH->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($results as $result) {
                                $mls_listing_ids[] = $result['mls_listing_id'];
                            }
                            return $mls_listing_ids;
                        }
                }
                
                catch (Exception $e) {
                    
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                        
                }
        }
        
        /**
         * Gets all mls_listing_id numbers with listing_date more recent than or equal to specific datetime.
         * 
         * @param string $datetime_ago_mysql datetime in MySQL formate.
         * 
         * @return mixed 
         */
        function getMlsListingIdsIncrementalByDaysAgo($days_ago)
        {
                $mls_listing_ids = array();
                try 
                {
                    $DBH = $this->connect(); 
                    if ($DBH == FALSE) 
                    {
                        $method = __METHOD__;
                        $error = "{$method} Failed to connect to database.";
                        throw new Exception($error);
                        return FALSE;
                    } 
                    else 
                    {
                        // Create datetime for $days_ago number of days ago and format to MySQL datetime.
                        $timezone = new DateTimeZone('UTC');
                        $datetime = new DateTime(NULL, $timezone);
                        $datetime->modify("-{$days_ago} days");
                        $days_ago_mysql = $datetime->format('Y-m-d H:i:s');
                        $sql = "SELECT mls_listing_id FROM {$this->db_table} WHERE listing_date >= '{$days_ago_mysql}'";
                        echo $sql;
                        $STH = $DBH->prepare($sql);
                        $STH->execute();
                        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($results as $result) 
                        {
                            $mls_listing_ids[] = $result['mls_listing_id'];
                        }
                        return $mls_listing_ids;

                    }
                }

                    catch (Exception $e) 
                    {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
            
        }
}