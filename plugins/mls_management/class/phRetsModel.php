<?php

class phRetsModel
{
        /**
         * RETS URL.
         * @var string
         */
        public $rets_url;

        /**
         * RETS username.
         * @var string 
         */
        public $rets_user;

        /**
         * RETS password.
         * @var string
         */
        public $rets_pass;

        /**
         * RETS version number.
         * @var string
         */
        public $rets_version;

        /**
         * RETS Profile.
         * @var string
         */
        public $rets_profile;
        
        /**
         * Names of MLS systems to access.
         * @var array
         */
        public $all_mls;
        
        /**
         * Usual constructor doing what they do. 
         */
        public function __construct($rets_url = NULL, $rets_user = NULL, $rets_pass = NULL, $rets_version = '1.7.2', $rets_profile='') 
        {
                // Get credentials for RETS.
                // TODO Change this so an instance is created for each MLS, rather than processing multiple MLS's in once object.
                //echo 'URL: ' . $rets_url;
                $this->rets_url      = $rets_url;
                $this->rets_user     = $rets_user;
                $this->rets_pass     = $rets_pass;
                $this->rets_version  = $rets_version; // REMOVE this after XML config provides RETS version.
                $this->rets_profile = $rets_profile;
        }
        
        /**
         * Opens connection to RETS system using credeentials in config file.
         * 
         * @return object
         * @throws Exception
         * 
         * TODO Eliminate this once phRETSimg is made to extend phRETS lib. 
         */
        public function connect()
        {   
            try 
            {
                // Instantiate phRETS lib. // TODO Rework phRETS lib; a lot could be improved.
                $rets = new phRETS;
                $rets->AddHeader("RETS-Version", "RETS/{$this->rets_version}");
                if(!empty($this->rets_profile))
                {
					$rets->AddHeader("User-Agent", $this->rets_profile);
				}
                // TODO Improve error handling.
                if ( ! $connect = $rets->Connect($this->rets_url, $this->rets_user, $this->rets_pass) ) {
                    throw new Exception('RETS connection failed.');
                } 
            }
            catch (Exception $e)
            {
                echo "Could not connect for some reason"; // Production.
                echo "\n($this->rets_url, $this->rets_user, $this->rets_pass, $this->rets_version, $this->rets_profile)"; 
            }
            return $rets;
        }
}
