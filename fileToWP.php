<?php
/**
 *  ---------------------------------------------------------------
 *  @file fileToWP.php
 *  ---------------------------------------------------------------
 *  @brief A class that parses a Text file which will be use to 
 *  post to WordPress.
 *  @endbrief 
 *  ---------------------------------------------------------------
 *  @details
 *  Parses a text file nakes a post to WordPress. By passing the
 *  sanitation routines done by WordPress wp_insert_post function 
 *  through a database update. 
 *  @enddetails
 *  
 *  @remarks
 *  Please make sure that "MY_WP_PATH" is properly
 *  defined to point to your WordPress "wp_load.php" location,
 *  This class relies heavily on the constants defined for database
 *  access in WordPress configuration file.
 *  @endremarks
 *  
 *  
 *  @cond DEFINE_AUTHOR
 *  @Author Jojo Kahanding
 *  @date May 03, 2014 11:01 PM
 *  Copyright 2011 Jojo Kahanding - All World rights reserve
 *  @endcond
 */
 
require_once("common.php");
require_once("textFileToSections.php");
require_once("dbase.php");
define('MY_WP_PATH', '..'); // NOTE: DEFINE THIS !
require_once( MY_WP_PATH . '/wp-load.php' );

class CFileToWP
{
	const DUMMY_CONTENT = "dummy value for wp_insert_post_data()"; 
	// string used to pass to our WordPress wp_insert_post_data
	
							
	//constuctor class
	function __construct()
	{
		// create member database
		// note constants supplied
		// here are from wordpress
		$this->m_dbase = new CDbase(DB_HOST, 
			DB_NAME, DB_USER, DB_PASSWORD);
		// create our parser 
		$this->m_parser = new CTextFileToSections();
		// initialize our section array
		$this->initSections();
	}

	//destructor class
	function __destruct()
	{
	}
	
	/**
	 *  @brief Does a post to WordPress. 
	 *  
	 *  @param [in] $filename textfile to be processed
	 *  @param [in] $userId WordPress id
	 *  @return TRUE if successful otherwise FALSE
	 *  
	 *  @details
	 *  Does a post to WordPress from a text file ($filename) 
	 *  using wp_insert_post. Bypassing the sanitation check 
	 *  of WordPress through a database update.
	 *  @enddetails
	 *  
	 */
	function postToWordPress($filename, $userId = 1)
	{
		$this->initSections();
		// process the file using our parser
		if (!$this->m_parser->processFile($filename,
			$this->m_sections))
			return FALSE;
		// now attempt fill our post array
		//
		// DEFINITION from WordPress of a post array
		// array(
		//  'ID'             => [ <post id> ] // Are you updating an existing post?
		//  'post_content'   => [ <string> ] // The full text of the post.
		//  'post_name'      => [ <string> ] // The name (slug) for your post
		//  'post_title'     => [ <string> ] // The title of your post.
		//  'post_status'    => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
		//  'post_type'      => [ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
		//  'post_author'    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
		//  'ping_status'    => [ 'closed' | 'open' ] // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
		//  'post_parent'    => [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
		//  'menu_order'     => [ <order> ] // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
		//  'to_ping'        => // Space or carriage return-separated list of URLs to ping. Default empty string.
		//  'pinged'         => // Space or carriage return-separated list of URLs that have been pinged. Default empty string.
		//  'post_password'  => [ <string> ] // Password for post, if any. Default empty string.
		//  'guid'           => // Skip this and let Wordpress handle it, usually.
		//  'post_content_filtered' => // Skip this and let Wordpress handle it, usually.
		//  'post_excerpt'   => [ <string> ] // For all your post excerpt needs.
		//  'post_date'      => [ Y-m-d H:i:s ] // The time post was made.
		//  'post_date_gmt'  => [ Y-m-d H:i:s ] // The time post was made, in GMT.
		//  'comment_status' => [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
		//  'post_category'  => [ array(<category id>, ...) ] // Default empty.
		//  'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
		//  'tax_input'      => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
		//  'page_template'  => [ <string> ] // Requires name of template file, eg template.php. Default empty.
		// );
		//
		// We are going to feed post_content a dummy value. Reason being
		// function wp_insert_post sanitizes our content
		// since some of the code snippets are in html or PHP we are going to
		// bypass this function by doing an update to the database
		// using the id returned from wp_insert_post
		$newPost = array (
			'post_title'    => $this->m_sections["TITLE"],
			'post_content'  => CFileToWP::DUMMY_CONTENT,
			'post_status'   => 'publish',
			'post_date'     => date('Y-m-d H:i:s'),
			'post_author'   => $userId,
			'post_type'     => 'post',
			'post_category' => array(0), 
			'tags_input'    =>array('dummy', 'a', 'b')
		);
		
		$postId = wp_insert_post($newPost);
		// check if succesful
		if (0 == $postId)
			return FALSE;
		// now we update WordPress table. The contents are stored in a table called
		// wp_posts. The value of trray['post_content'] after the sanitation and filtering
		// routines by wp_insert_post finally gets to this table in the column post_content 
		$sqlUpdate = sprintf("update wp_posts set post_content='%s' where id=%d",
			 mysql_escape_string ($this->m_sections['STORY']), 
			 $postId);
		return $this->m_dbase->query($sqlUpdate);
	}
	
	/**
	 *  @brief Initialize our array of "SECTION NAMES"
	 *  
	 *  @return Nothing
	 *  
	 *  @details Initialize our array of sections with the
	 *  requried "SECTION NAMES" we are interested in.
	 *  @enddetails
	 */
	protected function initSections()
	{
		// we are only interested in extracting 
		// TITLE and STORY from the text file.
		// Layout of the file is given in
		// the CTextFileToSecion class description
		$this->m_sections = array();
		$this->m_sections['TITLE'] = "";
		$this->m_sections['STORY'] = "";
	}
	
	private $m_dbase; // database class
	private $m_fileParser; // file parser 
	private $m_sections; // array of sections 
}
?>