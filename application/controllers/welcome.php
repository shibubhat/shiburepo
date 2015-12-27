<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {
		
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	function getConnect($folder){
		$hostname = '{imap.gmail.com:993/imap/ssl}'.$folder;
		$username = 'shibu.kmr@gmail.com';
		$password = 'shibu123';
		$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
	    return $inbox;
	}
	
	function getLabels(){
		$this->load->database();
	    $this->load->model('grabber');
		$inbox = $this->getConnect('INBOX');
		$folders = imap_list($inbox, "{imap.gmail.com:993/imap/ssl}", "*");
		
		$i=0;
		$folde= array();
		foreach ($folders as $folder) {
		    $fold= str_replace("{imap.gmail.com:993/imap/ssl}", " ",imap_utf7_decode($folder));
			if($this->grabber->check_label_exist(htmlentities($fold))==0){
				$folde[$i]['id'] = uniqid();			
				$folde[$i]['name']= htmlentities($fold);
				$i++;
			}
			
		}
		if(count($folde)>0){
			$this->grabber->insert_labels($folde);
		}
		//imap_close($inbox);
	}
	function getLabelMessage(){
		$this->load->database();
	    $this->load->model('grabber');
		$labels = $this->grabber->get_labels();
		$i=0;
		foreach($labels as $label){
		$inbox = $this->getConnect(trim($label['name']));
		$emails = imap_search($inbox,'All');
		$j=1;
		if($emails){
			foreach($emails as $email){
				$overview = imap_fetch_overview($inbox,$email,0);
					
						$labelMsg['fk_message'] = $overview[0]->uid.'_'.$overview[0]->udate.'_'.$overview[0]->msgno;
						$labelMsg['fk_label'] = $label['id'];
						//$labelMsg[$i]['ts'] = $overview[0]->udate;
						$i++;
						if(count($emails)==$j){
						
								$lastlabelMessage['ts'] = $overview[0]->udate;
								//$lastlabelMessage['ts'];
								$this->grabber->update_label($label['id'],$lastlabelMessage);
						}
						$j++;
						$this->grabber->insert_labelmessage($labelMsg);
			}
			
		}
		}
		
		imap_close($inbox);
	}
	
	function getMessagess(){
		$this->load->database();
	    $this->load->model('grabber');
		$labels = $this->grabber->get_labels();
		$i=0;
		$inbox = $this->getConnect('[Gmail]/All Mail');
		$emails = imap_search($inbox,'All');
		
		$output='';
		$getMessage=array();
		if($emails){
			foreach($emails as $email){
				$overview = imap_fetch_overview($inbox,$email,0);
				$getMessage['msg_html'] = mysql_real_escape_string($this->get_part($inbox, $email, "TEXT/HTML"));
				$getMessage['message_id'] = $overview[0]->uid.'_'.$overview[0]->udate.'_'.$overview[0]->msgno;
				$getMessage['subject'] = $overview[0]->subject;
				$attachments = '';
		//$message = imap_fetchbody($inbox,$email,2);
                //imap_fetchstructure($inbox, $email);
		
                $struct = imap_fetchstructure($inbox,$email);
				//print_R($struct);
                $contentParts = count(isset($struct->parts) ? $struct->parts : '');
                if ($contentParts >= 2) {
                    for ($i=2;$i<=$contentParts;$i++) {
                        $att[$i-2] = imap_bodystruct($inbox,$email,$i);
                    }	
                    for ($k=0;$k<sizeof($att);$k++) {
					if(!is_object($att[$k]->parameters)){
							if ($att[$k]->parameters[0]->value == "us-ascii" || $att[$k]->parameters[0]->value    == "US-ASCII") {
								if ($att[$k]->parameters[1]->value != "") {
								   $strFileName = $att[$k]->parameters[1]->value;
								   $strFileType = strrev(substr(strrev($strFileName),0,4));
								   $fileContent = imap_fetchbody($inbox,$email,2);
								   $this->downloadFile($strFileType,$strFileName,$fileContent);
									$attachments .= $strFileName . ',';
								}
							} elseif ($att[$k]->parameters[0]->value != "iso-8859-1" &&    $att[$k]->parameters[0]->value != "ISO-8859-1") {
								$strFileName = $att[$k]->parameters[0]->value;
								$strFileType = strrev(substr(strrev($strFileName),0,4));
								$fileContent = imap_fetchbody($inbox,$email,2);
								$this->downloadFile($strFileType,$strFileName,$fileContent);
								$attachments .= $strFileName . ',';
							
							}
							}
						
                    }
                }
               // $getMessage[$i]['num_attach_emb'] = $attachments;
			$this->grabber->insert_messages($getMessage);
			}
		}
		
		
		imap_close($inbox);
	}
	
	
function downloadFile($strFileType,$strFileName,$fileContent) {
    $ContentType = "application/octet-stream";
    if ($strFileType == ".asf") 
            $ContentType = "video/x-ms-asf";
    if ($strFileType == ".avi")
            $ContentType = "video/avi";
    if ($strFileType == ".doc")
            $ContentType = "application/msword";
    if ($strFileType == ".zip")
            $ContentType = "application/zip";
    if ($strFileType == ".xls")
            $ContentType = "application/vnd.ms-excel";
    if ($strFileType == ".gif")
            $ContentType = "image/gif";
    if ($strFileType == ".jpg" || $strFileType == "jpeg")
            $ContentType = "image/jpeg";
    if ($strFileType == ".wav")
            $ContentType = "audio/wav";
    if ($strFileType == ".mp3")
            $ContentType = "audio/mpeg3";
    if ($strFileType == ".mpg" || $strFileType == "mpeg")
            $ContentType = "video/mpeg";
    if ($strFileType == ".rtf")
            $ContentType = "application/rtf";
    if ($strFileType == ".htm" || $strFileType == "html")
            $ContentType = "text/html";
    if ($strFileType == ".xml") 
            $ContentType = "text/xml";
    if ($strFileType == ".xsl") 
            $ContentType = "text/xsl";
    if ($strFileType == ".css") 
            $ContentType = "text/css";
    if ($strFileType == ".php") 
            $ContentType = "text/php";
    if ($strFileType == ".asp") 
            $ContentType = "text/asp";
    if ($strFileType == ".pdf")
            $ContentType = "application/pdf";

    if (substr($ContentType,0,4) == "text") {
        file_put_contents($strFileName, imap_qprint($fileContent));
    } else {
        file_put_contents($strFileName, imap_base64($fileContent));
    }
}

	function get_part($stream, $msg_number, $mime_type, $structure = false,$part_number    = false) {
				$prefix='';
			if(!$structure) {
				$structure = imap_fetchstructure($stream, $msg_number);
			}
			if($structure) {
				if($mime_type == $this->get_mime_type($structure)) {
						if(!$part_number) {
								$part_number = "1";
						}
						$text = imap_fetchbody($stream, $msg_number, $part_number);
						if($structure->encoding == 3) {
								return imap_base64($text);
						} else if($structure->encoding == 4) {
								return imap_qprint($text);
						} else {
						return $text;
				}
}

        if($structure->type == 1) /* multipart */ {
        while(list($index, $sub_structure) = each($structure->parts)) {
                if($part_number) {
                        $prefix = $part_number . '.';
                }
                $data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure,$prefix .    ($index + 1));
                if($data) {
                        return $data;
                }
        } // END OF WHILE
        } // END OF MULTIPART
} // END OF STRUTURE
return false;
}

function get_mime_type(&$structure) {
    $primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
    if($structure->subtype) {
        return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
    }
    return "TEXT/PLAIN";
}
	
	public function indenx()
	{
		//echo "<pre>";
		//print_r($_SERVER['HTTP_HOST']);
		//exit;
		$hostname = '{imap.gmail.com:993/imap/ssl}test';;
		$username = 'smartianshibu@gmail.com';
		$password = 'smartian@shibu';

	$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
			
	

/* grab emails */
$emails = imap_search($inbox,'ALL');

$overview = imap_fetch_overview($inbox,1,0);

print_r($overview);exit;
/* if emails are returned, cycle through each... */
if($emails) {
	/* begin output var */
	$output = '';
	$values= '';
	/* put the newest emails on top */
	rsort($emails);
        /* for every email... */
	foreach($emails as $email) {
		
		/* get information specific to this email */
		$overview = imap_fetch_overview($inbox,$email,0);
		$message = get_part($inbox, $email, "TEXT/HTML");
		$attachments = '';
		//$message = imap_fetchbody($inbox,$email,2);
                //imap_fetchstructure($inbox, $email);
		
                
                $struct = imap_fetchstructure($inbox,$email);
                $contentParts = count($struct->parts);
                if ($contentParts >= 2) {
                    for ($i=2;$i<=$contentParts;$i++) {
                        $att[$i-2] = imap_bodystruct($inbox,$email,$i);
                    }
                    for ($k=0;$k<sizeof($att);$k++) {
                        if ($att[$k]->parameters[0]->value == "us-ascii" || $att[$k]->parameters[0]->value    == "US-ASCII") {
                            if ($att[$k]->parameters[1]->value != "") {
                               $strFileName = $att[$k]->parameters[1]->value;
                               $strFileType = strrev(substr(strrev($strFileName),0,4));
                               $fileContent = imap_fetchbody($inbox,$email,2);
                               downloadFile($strFileType,$strFileName,$fileContent);
			       $attachments .= $strFileName . ',';
                            }
                        } elseif ($att[$k]->parameters[0]->value != "iso-8859-1" &&    $att[$k]->parameters[0]->value != "ISO-8859-1") {
                            $strFileName = $att[$k]->parameters[0]->value;
                            $strFileType = strrev(substr(strrev($strFileName),0,4));
                            $fileContent = imap_fetchbody($inbox,$email,2);
                            //downloadFile($strFileType,$strFileName,$fileContent);
			    $attachments .= $strFileName . ',';
                        }
                    }
                }
				}
				}
				//	imap_msgno($inbox,$uid);	
				
			
	
		//exit;
		$this->load->view('welcome_message');
	}
/**
 * Get Message with given ID.
 *
 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
 * @param  string $userId User's email address. The special value 'me'
 * can be used to indicate the authenticated user.
 * @param  string $messageId ID of Message to get.
 * @return Google_Service_Gmail_Message Message retrieved.
 */
function getMessage($service, $userId, $messageId) {
	//$this->load->model('grabber');
  try {
    $message = $service->users_messages->get('me', $messageId);
    return $message;
  } catch (Exception $e) {
    print 'An error occurred: ' . $e->getMessage();
  }
}


 function dec2hex(){
  $hexvalues = array('0','1','2','3','4','5','6','7',
        '8','9','a','b','c','d','e','f');
  $hexval = '';
  $number='546db72177a44ed99ac86c7dac1802de';
   while($number != '0') {
      $hexval = $hexvalues[bcmod($number,'16')].$hexval;
   $number = bcdiv($number,'16',0);
  }
  echo $hexval;
 }

function index(){
  
	$this->load->library('google');
	$this->load->library('Session');
		//$this->session->unset_userdata('access_token');
	$client = new Google_Client();
	$client_id 	='965647569797-3rpebsateef53ks26hgduv8tib42n1tu.apps.googleusercontent.com';
	$client_secret ='04F_uhSpf-hUwe_oqbuZ6cVg';
	$redirect_uri = 'http://localhost/gmailgrabber';
	$client->setClientId($client_id);
	$client->setClientSecret($client_secret);
	$client->setRedirectUri($redirect_uri);
	$client->addScope("https://www.googleapis.com/auth/gmail.readonly");
	
 if (isset($_REQUEST['logout'])) {
	  $this->session->unset_userdata('access_token');
	}
	if (isset($_GET['code'])) {
	  $client->authenticate($_GET['code']);
	  $this->session->set_userdata('access_token',$client->getAccessToken());
	  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}
	

	if ($this->session->userdata('access_token')){
	  $client->setAccessToken($this->session->userdata('access_token'));
	} else {
	  $authUrl = $client->createAuthUrl();
}
	if (isset($authUrl)) {
  echo "<a class='login' href='" . $authUrl . "'>Connect Me!</a>";
} else {
 

$access= json_decode($this->session->userdata('access_token'));
 

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Gmail($client);
//$service= new Google_Service_Gmail($client_id);
$userId= "shibirana@gmail.com";
 $this->_listMessages($service, $userId,$access); 
 }
}

function _listMessages($service, $userId,$access=null) {
     	  Static $i =0;
	
  $pageToken = $access;
  $messages = array();
  $opt_param = array();
  do {
    try {
      if ($pageToken) {
       $opt_param['pageToken'] = $pageToken;
		$opt_param['maxResults'] = 50;
		//$opt_param['q'] = '';
      }
      $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);
	  echo "<pre>";
	  //print_r($messagesResponse);
	  
      if ($messagesResponse->getMessages()) {
        $messages = array_merge($messages, $messagesResponse->getMessages());
        //$pageToken = $messagesResponse->getNextPageToken();
		$pageToken = $messagesResponse->getNextPageToken();
      }
	   
    } catch (Exception $e) {
      print 'An error occurred: ' . $e->getMessage();
    }
  } while ($pageToken);

  foreach ($messages as $message) {
	//$this->getMessage($service, $userId, $message->getId());
    print 'Message with ID: ' . $message->getId() . '<br/>';
	$i++;
  }
  echo $i;
  $this->_listMessages($service, $userId,$pageToken);

  return $messages;
}


	function indsex(){
	
	
	/* 	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
		$username = 'smartianshibu@gmail.com';
		$password = 'smartian@shibu';

	$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
			
			
			    $header = imap_header($inbox,5);
				$overview = imap_fetch_overview($inbox,5,0);
					
				echo "<pre>";
			print_r($header);
			print_r($overview);
			exit;
			
			 */
			
				
	$this->load->library('session');
	$client_id 	='965647569797-3rpebsateef53ks26hgduv8tib42n1tu.apps.googleusercontent.com';
	$client_secret ='04F_uhSpf-hUwe_oqbuZ6cVg';
	$redirect_uri = 'http://localhost/gmailgrabber';
	$this->load->library('google');
	$this->google->setClientId($client_id);
	$this->google->setClientSecret($client_secret);
	$this->google->setRedirectUri($redirect_uri);
	$this->google->addScope("https://www.googleapis.com/auth/gmail.modify");
	$this->google->addScope("https://www.googleapis.com/auth/gmail.readonly");
	if (isset($_REQUEST['logout'])) {
	  $this->session->unset_userdata('access_token');
	}
	if (isset($_GET['code'])) {
	  $this->google->authenticate($_GET['code']);
	  $this->session->set_userdata('access_token',$this->google->getAccessToken());
	  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}
	if ($this->google->getAccessToken() && isset($_GET['url'])) {
	  $url = new Google_Service_Urlshortener_Url();
	  $url->longUrl = $_GET['url'];
	  $short = $service->url->insert($url);
	 $this->session->set_userdata('access_token',$this->google->getAccessToken());
	}

	if ($this->session->userdata('access_token')){
	  $this->google->setAccessToken($this->session->userdata('access_token'));
	} else {
	  $authUrl = $this->google->createAuthUrl();
}
	if (isset($authUrl)) {
  echo "<a class='login' href='" . $authUrl . "'>Connect Me!</a>";
} else {
  echo <<<END
    <form id="url" method="GET" action="{$_SERVER['PHP_SELF']}">
      <input name="url" class="url" type="text">
      <input type="submit" value="Shorten">
    </form>
    <a class='logout' href='?logout'>Logout</a>
END;


$access= json_decode($this->session->userdata('access_token'));
//print_r($access);
//exit;
}
//echo $this->google->getLibraryVersion(); 
	
	
	$this->load->helper('url');
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
		$username = 'smartianshibu@gmail.com';
		$password = 'smartian@shibu';

	$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
			
			
			    $header = imap_header($inbox,6);
				$overview = imap_fetch_overview($inbox,6,0);
				$data = imap_search($inbox,"X-GM-MSGID 1393947353372434321",null);
					
				echo "<pre>";
			//print_r($data);
				print_r($header);
				exit;
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */