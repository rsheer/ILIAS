<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This script provides a WebDAV interface for the ILIAS repository.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/

// Initialize
// -----------------------------------------------------

// Retrieve the client id from PATH_INFO
// Component 1 contains the ILIAS client_id.
$path_info_components = explode('/',$_SERVER['PATH_INFO']);
$client_id = $path_info_components[1];

// Set context for authentication
include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_HTTP);

// Launch ILIAS using the client id we have determined
$_GET["client_id"] = $client_id;

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEBDAV);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// Launch the WebDAV Server
include_once "Services/WebDAV/classes/class.ilWebDAVRequestHandler.php";
$server =  ilWebDAVRequestHandler::getInstance();
$server->handleRequest();
?>
