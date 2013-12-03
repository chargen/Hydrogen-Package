<?php

//
// _php/functions.php
//
// Author:
//   Matthew Davey <matthew.davey@dotbunny.com>
//
// Copyright (c) 2013 dotBunny Inc. (http://www.dotbunny.com)
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

function scrubText($text)
{
	$text = str_replace("\n", " ", $text);
	$text = str_replace("\r", " ", $text);
	$text = str_replace("\t", " ", $text);
	
	$text = str_replace(" . ", ". ", $text);
	$text = str_replace(" , ", ", ", $text);
	
	$text = str_replace("  ", " ", $text);

		
	return trim($text);
}

function findUnityPath($type)
{
	// Just check for the sake of checking
	$unity_path = $type . ".html";
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;
	
	$unity_path = str_replace("<FieldType>", "", $unity_path);
	$unity_path = str_replace("<ReturnType>", "", $unity_path);
	
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;

	$unity_path = str_replace("+", "-", $unity_path);	
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;
	
	$unity_path = str_replace("..", ".", $unity_path);
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;
	
	
	// Reset / Another Approach
	$unity_path = $type . ".html";
	$unity_path = str_replace("<FieldType>", "", $unity_path);
	$unity_path = str_replace("<ReturnType>", "", $unity_path);

	$unity_path = str_replace("+", ".", $unity_path);	
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;

	// Special Case For CTOR	
	$unity_path = str_replace("..ctor", "-ctor", $unity_path);
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;

	$unity_path = str_replace("..", ".", $unity_path);
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_path)) return SCRIPTREFERENCE_PATH . $unity_path;
	
	
	return null;
}

function findUnityMemberPath($type, $member)
{
	// Check Unity Pathing
	
	$unity_member_path = $type . "." . $member . ".html";
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_member_path)) return SCRIPTREFERENCE_PATH . $unity_member_path;
	
	$unity_member_path = str_replace("+", ".", $type) . "." . str_replace("+", ".", $member) . ".html";
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_member_path)) return SCRIPTREFERENCE_PATH . $unity_member_path;
	
	$unity_member_path = str_replace("+", ".", $type) . "-" . str_replace("+", ".", $member) . ".html";
	if ( file_exists(SCRIPTREFERENCE_PATH . $unity_member_path)) return SCRIPTREFERENCE_PATH . $unity_member_path;
	
	return null;
}

function cleanUpParameters($file)
{
	$file = str_replace(" hydrogen_parameter_tag=\"true\"", "", $file);
	$file = str_replace("<param name=\"DELETEME\">To be added.</param>\n", "", $file);
	
	return $file;
}
function scrubFile($file)
{
	// Strip Newlines
	$file = str_replace("\n", "", $file);
	
	$file = strip_tags($file, '<p><span><a><h3>');
	
	return $file;
}

function deleteDir($path)
{
    if (!is_dir($path)) {
        throw new InvalidArgumentException("$path is not a directory");
    }
    if (substr($path, strlen($path) - 1, 1) != '/') {
        $path .= '/';
    }
    $dotfiles = glob($path . '.*', GLOB_MARK);
    $files = glob($path . '*', GLOB_MARK);
    $files = array_merge($files, $dotfiles);
    foreach ($files as $file) {
        if (basename($file) == '.' || basename($file) == '..') {
            continue;
        } else if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($path);
}