<?php
//
// create.php
//
// Author:
//   Matthew Davey <matthew.davey@dotbunny.com>
//
// Copyright (c) 2014 dotBunny Inc. (http://www.dotbunny.com)
//
// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use,
// copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following
// conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE.

require_once("_php/config.php");
require_once("_php/functions.php");
require_once("_php/function.updateDocumentation.php");
require_once("_php/function.parseDocumentation.php");


// =================================================================================
//            Change These Settings To Reflect Your Install
// =================================================================================

// This will be used if a new function is found as the version it was added in
define("DOC_SINCE", "5.0.0b9");

// Reference Paths
define("FRAMEWORKS_PATH", "/Applications/Unity_5.0.0b9/Unity.app/Contents/Frameworks/Managed/");
define("SCRIPTREFERENCE_PATH","/Applications/Unity_5.0.0b9/Documentation/html/en/ScriptReference/");

// =================================================================================
//            No Settings Below This Line --- Change At Your Own Risk
// =================================================================================

// Establish Path
if(isset($_SERVER['PWD']))
{ $basepath = define("BASE_PATH", $_SERVER['PWD'] . "/"); }
else
{ $basepath = define("BASE_PATH", ereg_replace('[^/]*$', '', $_SERVER['PHP_SELF']) . "/"); }

// Create Directories
if (!is_dir(SOURCE_PATH) ) { mkdir(SOURCE_PATH); }
if (!is_dir(LOG_PATH) ) { mkdir(LOG_PATH); }
if (!is_dir(RELEASE_PATH) ) { mkdir(RELEASE_PATH); }

// Check System Locations
if (!is_dir(FRAMEWORKS_PATH)) { die("\n\rUnity Framework Files Not Found\n\r\n\r"); }
if (!is_dir(SCRIPTREFERENCE_PATH)) { die("\n\rUnity Script Reference Not Found\n\r\n\r"); }

// Remove Old Logs & Misc
deleteDir(LOG_PATH);
mkdir(LOG_PATH);

// Remove Previous Release
deleteDir(RELEASE_PATH);
mkdir(RELEASE_PATH);

// Find Framework Libraries To Deal With
$frameworks = findUnityFrameworks();

echo "===[ Stage 1 - Dump Unity Framework Definitions ]===\n\r";

foreach($frameworks as $framework)
{
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	if ( !is_dir(SOURCE_PATH . $framework_name) ) {
		mkdir(SOURCE_PATH . $framework_name);
	}

	exec("/usr/bin/monodocer -path: " . SOURCE_PATH . $framework_name . " -since:" . DOC_SINCE . " -pretty > " . LOG_PATH . "monodocer.log -assembly: " . FRAMEWORKS_PATH . 	$framework);
	
	echo "DONE\n\r";
}


echo "\n\r===[ Stage 2 - Create Type Map ]===\n\r";

foreach($frameworks as $framework)
{	
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	// Generate Content from Documentation
	$XML = simplexml_load_file(BASE_PATH . SOURCE_PATH . $framework_name . "/index.xml");
	
	// Create Type Map before going through documentation
	foreach( $XML->Types->Namespace as $NamespaceObject)
	{
		foreach ($NamespaceObject->Type as $TypeObject)
		{		
			$unity[strtolower((string)$NamespaceObject['Name'])][strtolower((string)$TypeObject['Name'])] = (string)$TypeObject['Kind'];
		}
	}
	
	echo "DONE.\n\r";

}

echo "\n\r===[ Stage 3 - Scrape Documentation ]===\n\r";

foreach($frameworks as $framework)
{	
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	// Generate Content from Documentation
	$XML = simplexml_load_file(BASE_PATH . SOURCE_PATH . $framework_name . "/index.xml");
	
	foreach( $XML->Types->Namespace as $NamespaceObject)
	{
		foreach ($NamespaceObject->Type as $TypeObject)
		{	
			updateDocumentation($framework_name, $NamespaceObject['Name'], $TypeObject['Name'], strip_tags($TypeObject['Kind']));
		}
	}
	
	echo "DONE.\n\r";

}



echo "\n\r===[ Stage 4 - Combine Documentation ]===\n\r";

foreach($frameworks as $framework)
{	
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	// Combine documentation
	exec("/usr/bin/mdassembler --ecma " . SOURCE_PATH . $framework_name . "/" . " --out " . RELEASE_PATH . $framework_name . " > " . LOG_PATH . "mdassembler.log");	
	
	echo "DONE.\n\r";
}

echo "\n\r===[ Stage 5 - Create Source File ]===\n\r";

$source_nodes = "";
$source_providers = "";

foreach($frameworks as $framework)
{	
	
	$framework_name = str_replace(".dll", "", $framework);
	
	$source_nodes .= '<node label="' . $framework_name . '" name="' . $framework_name . '" />';
	$source_providers .= '<source provider="ecma" basefile="' . $framework_name .'" path="Unity" />';
	
	
}

// Create Source File
file_put_contents(RELEASE_PATH . "Unity.source", '<?xml version="1.0"?><monodoc><node label="Unity" name="Unity">'. $source_nodes . '</node>' .  $source_providers . '</monodoc>');
		
echo "DONE\n\r";

echo "\n\r===[ Stage 6 - Create Visual Studio XML ]===\n\r";


foreach($frameworks as $framework)
{	
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	
	// Export VS Compatible Docs
	exec("/usr/bin/monodocs2slashdoc " . SOURCE_PATH . $framework_name . " --out=" . RELEASE_PATH . $framework_name . ".xml");
	
	echo "DONE\n\r";
}
	

echo "\n\r===[ Stage 7 - Move Things! ]===\n\r";

mkdir(RELEASE_PATH . "MonoDevelop");
mkdir(RELEASE_PATH . "VS");

foreach($frameworks as $framework)
{	
	echo $framework . "... ";
	
	$framework_name = str_replace(".dll", "", $framework);
	
	rename(RELEASE_PATH . $framework_name . ".xml", RELEASE_PATH . "VS/" . $framework_name . ".xml");
	rename(RELEASE_PATH . $framework_name . ".tree", RELEASE_PATH . "MonoDevelop/". $framework_name . ".tree");
	rename(RELEASE_PATH . $framework_name . ".zip", RELEASE_PATH . "MonoDevelop/" .$framework_name . ".zip");
	
	echo "DONE\n\r";
}

rename(RELEASE_PATH . "Unity.source", RELEASE_PATH . "MonoDevelop/Unity.source");

if ( DEBUG ) {

print "\nCounts\n";
print_r($counts);
print "\n";
}