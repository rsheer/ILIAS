<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilObjFileAccessSettings*
*
* This class encapsulates accesses to settings which are relevant for file
* accesses to ILIAS.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
*
* @version $Id$
*
* @extends ilObject
* @package WebDAV
*/

include_once "./Services/Object/classes/class.ilObject.php";

class ilObjFileAccessSettings extends ilObject
{
	/**
	 * Boolean property. Set this to true, to enable WebDAV access to files.
	 */
	private $webdavEnabled;

	/**
	 * Boolean property. Set this to true, to make WebDAV item actions visible for repository items.
	 */
	private $webdavActionsVisible;

	/**
	 * Boolean property. Set this to true, to use customized mount instructions.
	 * If the value is false, the default mount instructions are used.
	 */
	private $customWebfolderInstructionsEnabled;

	/**
	 * String property. Customized mount instructions for WebDAV access to files.
	 */
	private $customWebfolderInstructions;

	/**
	 * String property. Contains a list of file extensions separated by space.
	 * Files with a matching extension are displayed inline in the browser.
	 * Non-matching files are offered for download to the user.
	 */
	private $inlineFileExtensions;

	/** Boolean property. 
	 * 
	 * If this variable is true, the filename of downloaded
	 * files is the same as the filename of the uploaded file.
	 * 
	 * If this variable is false, the filename of downloaded
	 * files is the title of the file object.
	 */
	private $downloadWithUploadedFilename;

	/**
	* Constructor
	*
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "facs";
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	* Sets the webdavEnabled property.
	* 
	* @param	boolean	new value
	* @return	void
	*/
	public function setWebdavEnabled($newValue)
	{
		$this->webdavEnabled = $newValue;
	}
	/**
	* Gets the webdavEnabled property.
	* 
	* @return	boolean	value
	*/
	public function isWebdavEnabled()
	{
		return $this->webdavEnabled;
	}
	/**
	* Sets the webdavActionsVisible property.
	* 
	* @param	boolean	new value
	* @return	void
	*/
	public function setWebdavActionsVisible($newValue)
	{
		$this->webdavActionsVisible = $newValue;
	}
	/**
	* Gets the webdavActionsVisible property.
	* 
	* @return	boolean	value
	*/
	public function isWebdavActionsVisible()
	{
		return $this->webdavActionsVisible;
	}


	/**
	* Sets the customWebfolderInstructions property.
	* 
	* The webfolder instructions consist of HTML text, with placeholders.
	* See ilDAVServer::_getWebfolderInstructionsFor for a description of
	* the supported placeholders.
	* 
	* @param	string	HTML text with placeholders.
	* @return	void
	*/
	public function setCustomWebfolderInstructions($newValue)
	{
		$this->customWebfolderInstructions = $newValue;
	}
	/**
	* Gets the customWebfolderInstructions property.
	* 
	* @return	boolean	value
	*/
	public function getCustomWebfolderInstructions()
	{
		if (strlen($this->customWebfolderInstructions) == 0) 
		{
			require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
			$this->customWebfolderInstructions = self::_getDefaultWebfolderInstructions();
		}
		return $this->customWebfolderInstructions;
	}
	/**
	* Gets the defaultWebfolderInstructions property.
	* This is a read only property. The text is retrieved from $lng.
	* 
	* @return	String	value
	*/
	public function getDefaultWebfolderInstructions()
	{
		return self::_getDefaultWebfolderInstructions();
	}
	/**
	* Gets the customWebfolderInstructionsEnabled property.
	* 
	* @return	boolean	value
	*/
	public function isCustomWebfolderInstructionsEnabled()
	{
		return $this->customWebfolderInstructionsEnabled;
	}
	/**
	* Sets the customWebfolderInstructionsEnabled property.
	* 
	* @param	boolean new value.
	* @return	void
	*/
	public function setCustomWebfolderInstructionsEnabled($newValue)
	{
		$this->customWebfolderInstructionsEnabled = $newValue;
	}


	/**
	* Sets the inlineFileExtensions property.
	* 
	* @param	string	new value, a space separated list of filename extensions.
	* @return	void
	*/
	public function setInlineFileExtensions($newValue)
	{
		$this->inlineFileExtensions = $newValue;
	}
	/**
	* Gets the inlineFileExtensions property.
	* 
	* @return	boolean	value
	*/
	public function getInlineFileExtensions()
	{
		return $this->inlineFileExtensions;
	}
	/**
	* Sets the downloadWithUploadedFilename property.
	*
	* @param	boolean	
	* @return	void
	*/
	public function setDownloadWithUploadedFilename($newValue)
	{
		$this->downloadWithUploadedFilename = $newValue;
	}
	/**
	* Gets the downloadWithUploadedFilename property.
	*
	* @return	boolean	value
	*/
	public function isDownloadWithUploadedFilename()
	{
		return $this->downloadWithUploadedFilename;
	}


	/**
	* create
	*
	* note: title, description and type should be set when this function is called
	*
	* @return	integer		object id
	*/
	public function create()
	{
		parent::create();
		$this->write();
	}
	/**
	* update object in db
	*
	* @return	boolean	true on success
	*/
	public function update()
	{
		parent::update();
		$this->write();
	}
	/**
	* write object data into db
	* @param	boolean
	*/
	private function write()
	{
		global $DIC;
		$ilClientIniFile = $DIC['ilClientIniFile'];

		// Clear any old error messages
		$ilClientIniFile->error(null);
		
		if (! $ilClientIniFile->groupExists('file_access'))
		{
			$ilClientIniFile->addGroup('file_access');
		}
		$ilClientIniFile->setVariable('file_access', 'webdav_enabled', $this->webdavEnabled ? '1' : '0');
		$ilClientIniFile->setVariable('file_access', 'webdav_actions_visible', $this->webdavActionsVisible ? '1' : '0');
		$ilClientIniFile->setVariable('file_access', 'download_with_uploaded_filename', $this->downloadWithUploadedFilename ? '1' : '0');
		$ilClientIniFile->write();
		
        if ($ilClientIniFile->getError()) {
            global $DIC;
            $ilErr = $DIC['ilErr'];
			$ilErr->raiseError($ilClientIniFile->getError(),$ilErr->WARNING);
        }

		require_once 'Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('file_access');
		$settings->set('inline_file_extensions', $this->inlineFileExtensions);
		$settings->set('custom_webfolder_instructions_enabled', $this->customWebfolderInstructionsEnabled ? '1' : '0');
		$settings->set('custom_webfolder_instructions', $this->customWebfolderInstructions);
	}
	/**
	* read object data from db into object
	*/
	public function read()
	{
		parent::read();

		global $DIC;
		$ilClientIniFile = $DIC['ilClientIniFile'];
		$this->webdavEnabled = $ilClientIniFile->readVariable('file_access','webdav_enabled') == '1';
		$this->webdavActionsVisible = $ilClientIniFile->readVariable('file_access','webdav_actions_visible') == '1';
		$this->downloadWithUploadedFilename = $ilClientIniFile->readVariable('file_access','download_with_uploaded_filename') == '1';
		$ilClientIniFile->ERROR = false;

		require_once 'Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('file_access');
		$this->inlineFileExtensions = $settings->get('inline_file_extensions','');
		$this->customWebfolderInstructionsEnabled = $settings->get('custom_webfolder_instructions_enabled', '0') == '1';
		$this->customWebfolderInstructions = $settings->get('custom_webfolder_instructions', '');
	}

	/**
	 * Gets instructions for the usage of webfolders.
	 *
	 * The instructions consist of HTML text with placeholders.
	 * See _getWebfolderInstructionsFor for a description of the supported
	 * placeholders.
	 *
	 * @return String HTML text with placeholders.
	 */
	public static function _getDefaultWebfolderInstructions()
	{
	    global $lng;
	    return $lng->txt('webfolder_instructions_text');
	}
	
	/**
	 * Gets Webfolder mount instructions for the specified webfolder.
	 *
	 *
	 * The following placeholders are currently supported:
	 *
	 * [WEBFOLDER_TITLE] - the title of the webfolder
	 * [WEBFOLDER_URI] - the URL for mounting the webfolder with standard
	 *                   compliant WebDAV clients
	 * [WEBFOLDER_URI_IE] - the URL for mounting the webfolder with Internet Explorer
	 * [WEBFOLDER_URI_KONQUEROR] - the URL for mounting the webfolder with Konqueror
	 * [WEBFOLDER_URI_NAUTILUS] - the URL for mounting the webfolder with Nautilus
	 * [IF_WINDOWS]...[/IF_WINDOWS] - conditional contents, with instructions for Windows
	 * [IF_MAC]...[/IF_MAC] - conditional contents, with instructions for Mac OS X
	 * [IF_LINUX]...[/IF_LINUX] - conditional contents, with instructions for Linux
	 * [ADMIN_MAIL] - the mailbox address of the system administrator
	 
	 * @param String Title of the webfolder
	 * @param String Mount URI of the webfolder for standards compliant WebDAV clients
	 * @param String Mount URI of the webfolder for IE
	 * @param String Mount URI of the webfolder for Konqueror
	 * @param String Mount URI of the webfolder for Nautilus
	 * @param String Operating system: 'windows', 'unix' or 'unknown'.
	 * @param String Operating system flavor: 'xp', 'vista', 'osx', 'linux' or 'unknown'.
	 * @return String HTML text.
	 */
	public static function _getWebfolderInstructionsFor($webfolderTitle,
	    $webfolderURI, $webfolderURI_IE, $webfolderURI_Konqueror, $webfolderURI_Nautilus,
	    $os = 'unknown', $osFlavor = 'unknown')
	{
	    global $ilSetting;
	    
	    $settings = new ilSetting('file_access');
	    $str = $settings->get('custom_webfolder_instructions', '');
	    if (strlen($str) == 0 || ! $settings->get('custom_webfolder_instructions_enabled'))
	    {
	        $str = self::_getDefaultWebfolderInstructions();
	    }
	    if(is_file('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm')){
	        $str = fread(fopen('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm', "rb"),filesize('Customizing/clients/'.CLIENT_ID.'/webdavtemplate.htm'));
	    }
	    $str=utf8_encode($str);
	    
	    preg_match_all('/(\\d+)/', $webfolderURI, $matches);
	    $refID=end($matches[0]);
	    
	    $str = str_replace("[WEBFOLDER_ID]", $refID, $str);
	    $str = str_replace("[WEBFOLDER_TITLE]", $webfolderTitle, $str);
	    $str = str_replace("[WEBFOLDER_URI]", $webfolderURI, $str);
	    $str = str_replace("[WEBFOLDER_URI_IE]", $webfolderURI_IE, $str);
	    $str = str_replace("[WEBFOLDER_URI_KONQUEROR]", $webfolderURI_Konqueror, $str);
	    $str = str_replace("[WEBFOLDER_URI_NAUTILUS]", $webfolderURI_Nautilus, $str);
	    $str = str_replace("[ADMIN_MAIL]", $ilSetting->get("admin_email"), $str);
	    
	    if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false){
	        $str = preg_replace('/\[IF_IEXPLORE\](?:(.*))\[\/IF_IEXPLORE\]/s','\1', $str);
	    }else{
	        $str = preg_replace('/\[IF_NOTIEXPLORE\](?:(.*))\[\/IF_NOTIEXPLORE\]/s','\1', $str);
	    }
	    
	    switch ($os)
	    {
	        case 'windows' :
	            $operatingSystem = 'WINDOWS';
	            break;
	        case 'unix' :
	            switch ($osFlavor)
	            {
	                case 'osx' :
	                    $operatingSystem = 'MAC';
	                    break;
	                case 'linux' :
	                    $operatingSystem = 'LINUX';
	                    break;
	                default :
	                    $operatingSystem = 'LINUX';
	                    break;
	            }
	            break;
	        default :
	            $operatingSystem = 'UNKNOWN';
	            break;
	    }
	    
	    if ($operatingSystem != 'UNKNOWN')
	    {
	        $str = preg_replace('/\[IF_'.$operatingSystem.'\](?:(.*))\[\/IF_'.$operatingSystem.'\]/s','\1', $str);
	        $str = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s','', $str);
	    }
	    else
	    {
	        $str = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s','\2', $str);
	    }
	    return $str;
	}
	
	/**
	 * Gets the maximum permitted upload filesize from php.ini in bytes.
	 *
	 * @return int Upload Max Filesize in bytes.
	 */
	private function getUploadMaxFilesize() {
	    $val = ini_get('upload_max_filesize');
	    
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	    
	    return $val;
	}

} // END class.ilObjFileAccessSettings
// END WebDAV
?>
