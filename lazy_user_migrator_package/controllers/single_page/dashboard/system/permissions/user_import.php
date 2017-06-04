<?php
namespace Concrete\Package\LazyUserMigratorPackage\Controller\SinglePage\Dashboard\System\Permissions;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\UserList;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Logging\Logger;
use Concrete\Core\Logging\LogEntry;
use Loader;
use Core;

class UserImport extends DashboardPageController
{
	
	public $l_stats = array();
	
	public function __construct($c) {
	  parent::__construct($c);
	  $this->l_stats = array('added' => 0, 'skipped' => 0, 'attribute_omitted' => array());
	  $this->LUM_VALIDATE = true;
	  $this->LUM_CHECK_ATTRIBUTES = false;
	  
	  $this->l = new Logger('LazyUserMigrate');
      set_time_limit(0);
	}
	
    function createUser($uName, $uEmail, $uPassword = null, $uAttributes = null, $uGroups = null)
    {
      if (null !== UserInfo::getByEmail($uEmail) || null !== UserInfo::getByUserName($uName)) {
        return false;
      }
      if (null !== ($uName = $uName ?: $uEmail)) { // Default to email as username if none set
        //$this->l_stats['added']++;
		$ui =  Core::make('Concrete\Core\User\RegistrationServiceInterface')->create(array( 'uName' => $uName,
            'uEmail' => $uEmail,
            'uPassword' => $uPassword,
            'uIsValidated' => $this->LUM_VALIDATE));
		/*	
        $ui = UserInfo::add(
            array( 'uName' => $uName,
            'uEmail' => $uEmail,
            'uPassword' => $uPassword,
            'uIsValidated' => $this->LUM_VALIDATE),
            [UserInfo::ADD_OPTIONS_NOHASH]
        );
		*/
        foreach ($uAttributes as $attHandle => $attValue) {
			//$this->l->debug("Found user attribute " . $attHandle . " for " . $attValue);
            // If set to check, validate that the attribute in the 'from' site is in the 'to' site
            if (!$this->LUM_CHECK_ATTRIBUTES || \UserAttributeKey::getByHandle($attHandle)) {
                try {
                    $ui->setAttribute($attHandle, $attValue);
					//$this->l->debug("Set user attribute for ". $uName .": " . $attHandle . " to " . $attValue);
                } catch (Exception $e) {
                    $this->l->error("Error setting attribute $attHandle for $uName. Check that attribute exists in target and is of the correct type.");
                }
            } else {
                $this->l_stats['attribute_omitted'][$attHandle] = 1;
            }
        }
        foreach ($uGroups as $gid => $groupName) {
            if (null === ($group = \Group::getByName($groupName))) {
				// If no group restriction or group name not in list of excluded groups
				if (($this->donotcreategroups == '') || (($this->donotcreategroups != '') && (!in_array($groupName, $this->excludedgroups)))) {
				  // Add group if it does not exist
				  //$this->l->debug("Adding group ".$groupName.". Is it in array of excludedgroups? ".in_array($groupName, $this->excludedgroups))
				  $group = \Group::add($groupName, str_replace('_', ' ', $groupName));
				}
		    }
            if (null !== ($group = \Group::getByName($groupName))) {
                try {
                    $ui->getUserObject()->enterGroup($group);
                } catch (Exception $e) {
                    $this->l->error("Error adding $uName to $groupName.");
                }
            }
        }
      }
      return true;
    }	
	
    public function view()
    {

    }
	
    public function import()
    {
	    $this->excludedgroups = array();
	    $this->donotcreategroups = trim($_POST['donotcreategroups']);
	    if ($this->donotcreategroups != '') {
          $this->excludedgroups = explode(',', $this->donotcreategroups);
        }	
		
		$mode = $_POST['mode'];
		
		if ($mode == 'xml') {
		  $this->importxml();
		} else if ($mode == 'json') {
		  $this->importjson();
		} else {
		  $this->error->add('Unknown mode specified');	
		}
		
		if ($this->LUM_CHECK_ATTRIBUTES && sizeof($this->l_stats['attribute_omitted'])) {
          $this->l->debug("Attributes omitted: " . implode(', ', array_keys($this->l_stats['attribute_omitted'])));
        }
        $this->l->info("Import: new users created: {$this->l_stats['added']}; users already in database: {$this->l_stats['skipped']}");
        //$this->l->close();
		
		$this->set("message", "Import: new users created: {$this->l_stats['added']}; users already in database: {$this->l_stats['skipped']}");
		
    }	

    public function importxml()
    {	
	  Loader::model('user_list');
	  $filename = $_FILES["fileToUpload"]["tmp_name"];
	  if ($filename != '') {
        $doc = new \DOMDocument();
        $doc->loadXML(file_get_contents($filename));
	  
	    $restrictedusers = array();
	    $restricttousers = trim($_POST['restricttousers']);
	    if ($restricttousers != '') {
          $restrictedusers = explode(',', $restricttousers);
        }	  
	  
        foreach ($doc->getElementsByTagName('User') as $user) {
		  if (($restricttousers == '') || (($restricttousers != '') &&  (in_array($user->getElementsByTagName('name')->item(0)->textContent, $restrictedusers)))) {
		
            $uAttributes = array();
			/*
		    $this->l->debug('Number of Attributes tags found: '. count($user->getElementsByTagName('Attributes')));
			if (count($user->getElementsByTagName('Attributes')) > 0) {
				$this->l->debug('Number of Child nodes for first attribute tag found: '. count($user->getElementsByTagName('Attributes')->item(0)->childNodes));
				if (count($user->getElementsByTagName('Attributes')->item(0)->childNodes) > 0) {
					$firstnode = $user->getElementsByTagName('Attributes')->item(0)->childNodes->item(0);
					$this->l->debug('Tag name and value of first child node found is: '. $firstnode->tagName . ' ' . $firstnode->textContent);
				}
			}
			*/
            foreach ($user->getElementsByTagName('Attributes')->item(0)->childNodes as $ua) {
			  //$this->l->debug("Found attributes : " . $ua->tagName . " with value " . $ua->textContent);  
              $uAttributes[$ua->tagName] = $ua->textContent;
            }
            $uGroup = array();
            foreach ($user->getElementsByTagName('Groups')->item(0)->childNodes as $group) {
              $uGroup[$group->getAttribute('id')] = $group->textContent;
            }
			
			$newPassword = '';
            $chars = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
            for ($i = 0; $i < 9; ++$i) {
                $newPassword .= substr($chars, rand() % strlen($chars), 1);
            }
			
            if ($this->createUser(
              $user->getElementsByTagName('name')->item(0)->textContent,
              $user->getElementsByTagName('email')->item(0)->textContent,
              $newPassword,
			  //$user->getElementsByTagName('raw_pass')->item(0)->textContent,
              $uAttributes,
              $uGroup
            ) ) {
              $this->l_stats['added']++;
            } else {
              $this->l_stats['skipped']++;
            }
		
		  }
        }
      } else {
        $this->error->add('You must specify an XML file to import');
      }	  
	}
	
    public function importjson()
    {	
      Loader::model('user_list');	
	  $filename = $_FILES["fileToUpload"]["tmp_name"];
	  if ($filename != '') {
        $json = json_decode(file_get_contents($filename), true);

	    $restrictedusers = array();
	    $restricttousers = trim($_POST['restricttousers']);
	    if ($restricttousers != '') {
          $restrictedusers = explode(',', $restricttousers);
        }	  

        foreach ($json['Users'] as $user) {
		  if (($restricttousers == '') || (($restricttousers != '') &&  (in_array($user['name'], $restrictedusers)))) {
			$newPassword = '';
            $chars = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
            for ($i = 0; $i < 9; ++$i) {
                $newPassword .= substr($chars, rand() % strlen($chars), 1);
            }
			
			$attributes = array();
            foreach ($user['attributes'] as $attValue) {
			  //$this->l->debug("Found user attribute " . key($attValue) . " for " . $attValue[key($attValue)]);
              if ($attValue[key($attValue)] != '') {			  
			    $attributes[key($attValue)] = $attValue[key($attValue)];
			  }
			}

			$groups = array();
            foreach ($user['groups'] as $grpValue) {
			  //$this->l->debug("Found user group " . key($grpValue) . " for " . $grpValue[key($grpValue)]);
              if ($grpValue[key($grpValue)] != '') {			  
			    $groups[key($grpValue)] = $grpValue[key($grpValue)];
			  }
			}			
			
			//$this->l->debug("User attributes: ".print_r($attributes, true));
			
            if ($this->createUser($user['name'], $user['email'], $newPassword, $attributes, $groups)) {
              $this->l_stats['added']++;
            } else {
              $this->l_stats['skipped']++;
            }
			
		  }
        }
      }	else {
        $this->error->add('You must specify a JSON file to import');
      }	
	
	}	
}

?>