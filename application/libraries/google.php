<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
set_include_path(APPPATH . 'third_party/' . PATH_SEPARATOR . get_include_path());
require_once APPPATH . 'third_party/Google/Client.php';
require_once APPPATH . 'third_party/Google/Config.php';
require_once APPPATH . 'third_party/Google/Auth/OAuth2.php';
require_once APPPATH . 'third_party/Google/Http/Request.php';
require_once APPPATH . 'third_party/Google/Utils.php';
require_once APPPATH . 'third_party/Google/IO/Curl.php';
require_once APPPATH . 'third_party/Google/IO/Abstract.php';
require_once APPPATH . 'third_party/Google/Http/CacheParser.php';
require_once APPPATH . 'third_party/Google/Auth/Exception.php';
require_once APPPATH . 'third_party/Google/Exception.php';
require_once APPPATH . 'third_party/Google/Service/Gmail.php';
require_once APPPATH . 'third_party/Google/Service.php';
  require_once APPPATH . 'third_party/Google/Http/REST.php';
  require_once APPPATH . 'third_party/Google/Cache/File.php';
    require_once APPPATH . 'third_party/Google/Cache/Abstract.php';
class Google extends Google_Client {
    function __construct($params = array()) {
        parent::__construct();
    }
} 

?>