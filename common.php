<?php
/**
 *  ---------------------------------------------------------------
 *  @file common.php
 *  ---------------------------------------------------------------
 *  @brief Contains common routines used by this module.
 *  @endbrief
 *  ---------------------------------------------------------------
 *  @details
 * 	Contains all common routines and must be included(include_once) 
 *	in every php program.
 *  @enddetails 

 *  @remarks
 *  Debuging mode is turned on and off here allowing for
 *  display of PHP parsing errors.
 *  @endremarks
 *  
 *  @cond DEFINE_AUTHOR
 *  @Author Jojo Kahanding
 *  @date April 27, 2014 10:11 PM
 *  Copyright 2011 Jojo Kahanding - All World rights reserve
 *  @endcond
 */

 /**
 *  @brief Turns on or off the PHP error reporting.
 *  
 *  @param [in] $on turns on/off PHP's error reporting
 *  @return Nothing
 *  
 *  @details This turns on or off the error_reporting functions
 *  	of PHP.
 *  @enddetails
 */
 function debugOn($on)
 {
	error_reporting($on ? E_ALL : 0);
	ini_set('display_errors', $on ? 1 : 0);
 }
 
/**
 *  @brief Used for debugging messages inserted in the code.
 *  
 *  @param [in] $message to be displayed
 *  @return Nothing.
 *  
 *  @details Trace and debug messages implementation.
 */
function DEBUGMESSAGE($message)
{
	$eol = "</BR>";
	echo $message . $eol;
}

/**
 *  @brief Dumps a variable
 *  
 *  @param [in] $var variable
 *  @return void
 *  
 *  @details Dumps a variable on screen.
 *  Used for debugging.
 *  @enddetails
 */
function DEBUGVAR($variable)
{
	DEBUGMESSAGE(print_r($variable,true));
}

// Turn on or off for Error Reporting
debugOn(TRUE);
?>