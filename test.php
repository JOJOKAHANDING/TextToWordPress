<?php
/**
 *  ---------------------------------------------------------------
 *  @file test.php
*  ---------------------------------------------------------------
 *  @brief Contains test routines for the classes defined in this
 *  module.
 *  @endbrief 
*  ---------------------------------------------------------------
 *  @details
 *  Implementation of various test functions used for debugging
 *  and verifying the classes and functions implemented in this
 *  module
 *  @enddetails
 *  
 *  @cond DEFINE_AUTHOR
 *  @author: Jojo Kahanding
 *  @date: May 04, 2014 09:54 AM
 *  Copyright 2011 Jojo Kahanding - All World rights reserve
 *  @endcond
 */

	require_once("common.php");
	require_once("textFileToSections.php");
	require_once("dbase.php");
	require_once('fileToWP.php');

	
	define('MY_WP_PATH', '..');	
	require_once( MY_WP_PATH . '/wp-load.php' );
	
	/**
	 *  @brief Test the CTextFileToSections class.
	 *  
	 *  @return Nothing
	 *  
	 *  @details unit test for CTextFileToSection class
	 */
	function testTextFileToSections()
	{
		$a =  new CTextFileToSections();
		$tags = array();
		$tags["TITLE"] = "";
		$tags["STORY"] = "";
		$tags["COMMENT"] = "";
		$tags["TAG"] = "";
		$a->processFile("testdata/input.data", $tags);
		//DEBUGVAR($tags);
		
		$user_ID = 1;
		$new_post = array(
			'post_title'    => $tags["TITLE"],
			'post_content'  => $tags["STORY"],
			'post_status'   => 'publish',
			'post_date'     => date('Y-m-d H:i:s'),
			'post_author'   => $user_ID,
			'post_type'     => 'post',
			'post_category' => array(0), //array(0)
			'tags_input'    =>array('jojo', 'code', 'test'),
				'no_filter' => true 
		);

		function filter_handler( $data , $postarr ) 
		{
		  DEBUGVAR($postarr);
		  DEBUGVAR($data);
		  str_replace($postarr['post_content'], '[CODE]','<code>');
		  str_replace($postarr['post_content'], '[/CODE]','</code>');
		  return $data;
		}
		
		remove_all_filters("content_save_pre");
		//add_filter( 'wp_insert_post_data', 'filter_handler', '99', 2 );
		$post_id = wp_insert_post($new_post);
		DEBUGMESSAGE($post_id);
	}

	/**
	 *  @brief Test the CDbase class
	 *  
	 *  @return Nothing
	 *  
	 *  @details Unit test for CDbase class.
	 *  @enddetails
	 *  @remarks
	 *  Please make sure that this module points to the correct
	 *  WP_PATH. The constants for the database are defined
	 *  in WordPress configuration.
	 *  @endremarks
	 *  
	 */
	 function testDbase()
	{
	
		DEBUGMESSAGE("Create");
		$dbase = new CDbase(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
		DEBUGMESSAGE("END Create");

		if ($dbase->query("select * from wp_posts limit 10"))
		{
			while ($row = $dbase->fetch())
			{
				DEBUGVAR($row);
			}
		}
	}
	
	$test = new CFileToWP();
	$test->postToWordPress("testdata/input.data");

?>