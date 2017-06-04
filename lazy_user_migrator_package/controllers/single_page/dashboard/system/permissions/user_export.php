<?php
namespace Concrete\Package\LazyUserMigratorPackage\Controller\SinglePage\Dashboard\System\Permissions;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\UserList;
use Concrete\Core\User\UserInfo;
use Loader;

class UserExport extends DashboardPageController
{
    public function view()
    {

    }
	
    public function export()
    {
		
		$mode = $_POST['mode'];
		
		if ($mode == 'xml') {
		  $this->exportxml();
		} else if ($mode == 'json') {
		  $this->exportjson();
		} else {
		  $this->error->add('Unknown mode specified');	
		}

    }	
	
    public function exportjson()
    {
      try {		
        Loader::model('user_list');
		
        $users = array();
        foreach ((new UserList())->get() as $user) {
           $attributes = array();
           $attribs = \UserAttributeKey::getList();
           foreach ($attribs as $key) {
		     $keyid = $key->getAttributeKeyHandle();
		     $value = $user->getAttribute($key);
		     if (is_string($keyid)) {
                $attributes[] = array($keyid => $value);
             }
           }	
          /*		   
          foreach (UserAttributeKey::getAttributes($user->getUserID()) as $key => $uak) {
            if (is_string($uak)) {
              $attributes[] = array($key => $uak);
            }
          }
		  */
          $groups = array();
		  $userGroups = $user->getUserObject()->getUserGroups();
          foreach ($userGroups as $gID) {
			$group = \Group::getById($gID)->getGroupName();
            $groups[] = array($gID => $group);
          }
          $users['Users'][] = array(
            'name' => $user->getUserName(),
            'email' => $user->getUserEmail(),
            'raw_pass' => $user->getUserPassword(),
            'attributes' => $attributes,
            'groups' => $groups
          );
        }	

		 $jsonOutput = json_encode($users);
		
         header('Cache-Control: no-cache, must-revalidate');
         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
         header("Content-Type: application/json");
         header('Content-Disposition: attachment; filename="export.json"' );
         header('Content-Length: ' . strlen($jsonOutput));	
         echo $jsonOutput;
         exit();		
	   
      } catch (Exception $ex) {
        $this->error->add($ex->getMessage());
      }		   
    }	
	
    public function exportxml()
    {
		
     try {		
       Loader::model('user_list');
       if (class_exists('DOMDocument')) {
         $dom = new \DOMDocument('1.0', 'UTF-8');
         $dnode = $dom->createElement('Users');
         $docNode = $dom->appendChild($dnode);

         foreach ((new UserList())->get(3000) as $user) {
           $ui = UserInfo::getByID($user->getUserID());
           $node = $dom->createElement('User');
           $userNode = $docNode->appendChild($node);

           $node = $dom->createElement('name');
           $cdata = $node->ownerDocument->createCDATASection($user->getUserName());
           $node->appendChild($cdata);
           $userNode->appendChild($node);

           $node = $dom->createElement('email');
           $cdata = $node->ownerDocument->createCDATASection($user->getUserEmail());
           $node->appendChild($cdata);
           $userNode->appendChild($node);

           $node = $dom->createElement('raw_pass');
           $cdata = $node->ownerDocument->createCDATASection($user->getUserPassword());
           $node->appendChild($cdata);
           $userNode->appendChild($node);

           $node = $dom->createElement('Attributes');
           $attrNode = $userNode->appendChild($node);
	
           $attribs = \UserAttributeKey::getList();
           foreach ($attribs as $key) {
		     $keyid = $key->getAttributeKeyHandle();
		     $value = $user->getAttribute($key);
		     if (is_string($keyid)) {
               $node = $dom->createElement($keyid);
               $cdata = $node->ownerDocument->createCDATASection($value);
               $node->appendChild($cdata);
               $attrNode->appendChild($node);
             }
           }
	
           $node = $dom->createElement('Groups');
           $groupNode = $userNode->appendChild($node);
           foreach ((array) $user->getUserObject()->getUserGroups() as $gID => $group) {
             $node = $dom->createElement("group");
             $cdata = $node->ownerDocument->createCDATASection($group);
             $node->appendChild($cdata);
             $groupAttr = $dom->createAttribute('id');
             $groupAttr->value = $gID;
             $node->appendChild($groupAttr);
             $groupNode->appendChild($node);
           }
         }

         $xmlOutput = $dom->saveXML();

         header('Cache-Control: no-cache, must-revalidate');
         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
         header("Content-Type: application/xml");
         header('Content-Disposition: attachment; filename="export.xml"' );
         header('Content-Length: ' . strlen($xmlOutput));	
         echo $xmlOutput;
         exit();	
       } else {
	     $this->error->add('Could not load DOMDocument class');
       }	
      } catch (Exception $ex) {
        $this->error->add($ex->getMessage());
      }	
    }	
	
}
?>