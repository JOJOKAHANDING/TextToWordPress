<?php
/**
 *  ---------------------------------------------------------------
 *  @file   textFileToSections.php
 *  ---------------------------------------------------------------
 *	@brief  A class that parses a text file and extracts sections 
	of text.
	@endbrief
 *  ---------------------------------------------------------------
 *	@details 
 *  This class is fed a text file, results are stored in
 *  an array indexed by "SECTION NAME"'s. This is used for populating 
 *  rapid populating WordpPess blogs. 
 *  
 *  The text File looks like this
 *  @code{.txt}
 *  ...
 *  [SECTION NAME]:
 *  Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
 *  Praesent vitae volutpat ante. Nunc elementum ligula metus, 
 *  eu blandit eros tempor et. 
 *  Proin vehicula condimentum tincidunt. Vivamus consectetur 
 *  congue orci, et aliquam eros. 
 *  Vestibulum ac consectetur tellus. 
 *  Proin pellentesque urna libero, 
 *  ut iaculis elit bibendum in. Fusce non iaculis lectus. 
 *  <code>
 *  for (i=0; i < size; ++i)
 *  {
 *  	x = p << 2;
 *  }
 *  </code>
 *  ...
 *  @endcode
 *  
 *  The contents of the array[SECTION NAME] is in a html WordPress 
 *  acceptable format. WordPress eol(end of lines)'s are added 
 *  if the succeending line is a blank. Also, lines in sections  
 *  starting with [code] tags and ending with a [/code] are 
 *  terminated  with a WordPress eol.
 *  @enddetails
 *  
 *  @cond DEFINE_AUTHOR
 *  @author Jojo Kahanding
 *  @date   April 30, 2014 07:05 PM
 *  Copyright 2011 Jojo Kahanding - All World rights reserve
 *  @endcond
 *  
 */
 
 include_once("common.php");
 
 class CTextFileToSections
 {
 	const EOL_WORDPRESS = '<BR/>'; // end of line of WordPress
	const EOL_UNIX = "\n"; // end of line of text file
	const EOL_WINDOWS = "\r\n";
	const TAB = "\t";
	const SPACE = ' ';
	const EMPTY_STRING = '';
	const EOW_SECTION = ":"; // end of word defining a section
	
	//constuctor class
	function __construct()
	{
	}

	//destructor class
	function __destruct()
	{
	}
	/**
	 *  @brief Parses a file to populate an array(indexed with 
	 *  "SECTION NAME"'s) 
	 *  @endbrief
	 *  
	 *  @param [in] $path filename
	 *  @param [in, out] $sectionArray an array indexed with "SECTION NAME"
	 *  @return true if succesful otherwise false
	 *  
	 *  @details
	 *  The file $path is processed based on the $sectionArray. The 
	 *  result of the parsing operation is store in $sectionArray
	 *  @enddetails
	 *  
	 *  @remarks
	 *  You can derive this method and do your own implementation.
	 *  @endremarks
	 */
	function processFile($path, &$sectionArray)
	{
		// initialize the sectionArray
		foreach ($sectionArray as $sectionName=>$section)
		{
			$sectionArray[$sectionName] = "";
		} 
		// file to array
		$lines = file($path, FILE_IGNORE_NEW_LINES);
		// initialize values
		$currentSection = "";
		$isCode = false;
		foreach ($lines as $line)
		{
			// process all lines a line a time
			$this->processLine($line, 
								$currentSection, 
								CTextFileToSections::EOL_WORDPRESS, 
								$sectionArray, 
								$isCode);
		}
		// format the sections to make it WordPress
		// acceptable
		foreach ($sectionArray as $sectionName => $section)
		{
			// terminate the section with a WordPress EOL
			$sectionArray[$sectionName] .= 
				CTextFileToSections::EOL_WORDPRESS;
				
		} 		
		return TRUE;
	}
	/**
	 *  @brief Processes a line of text.
	 *  @endbrief
	 *  @param [in] $line line to process
	 *  @param [in,out] $currentSectionName current "SECTION NAME" 
	 *  @paran [in] $eol end of line, terminator to be added to the
	 *  array if required.
	 *  @endparam
	 *  @param [in,out] $sectionArray array of strings indexed by 
	 *  	"SECTION NAMES"'s.
	 *  @endparam
	 *  @param [in,out] $isCode running flag containing the current 
	 *  	state of processing the [code] tag.
	 *  @code{.php}	 
	 *  	if <code> is found
	 *  		$isCode is turned on.
	 *  	if </code>	 
	 *  		$isCode is turned off.
	 *  	if $isCode
	 *  		each line is terminated with $eol
	 *  @endcode
	 *  @endparam	 
	 *  @return Nothing
	 *  
	 *  @details processess a Line and stores the info in the an 
	 *  	array of sections.
	 *  @enddetail
	 *  
	 */
	protected function processLine($line, 
		&$currentSectionName, 
		$eol, 
		&$sectionArray, 
		&$isCode)
	{
		$words = $this->extractWords($line);
		$countOfWords = $this->countOfWords($words);
		$firstWord = $countOfWords == 0 ? "" : $words[0];
		$nextSectionName = "";
		
		if ($this->isASectionName($firstWord, 
									$nextSectionName))
		{
			$currentSectionName = $nextSectionName; 
			return;
		}
		
		if (strcasecmp($firstWord, "<code>") == 0)
		{
			$isCode = TRUE;
		}
		else if (strcasecmp($firstWord, "</code>") == 0)
		{
			$isCode = FALSE;
		}
		
		if (array_key_exists($currentSectionName, $sectionArray))
		{	// add the line
			$sectionArray[$currentSectionName] .= $line;
			// add an eol if this is a code or a blank line
			if ($isCode || ($countOfWords == 0) )
			{
				$sectionArray[$currentSectionName] .= $eol;
			}
			else
			{
				// terminate with a space 
				$sectionArray[$currentSectionName] .= 
					CTextFileToSections::SPACE;
			}
		}
		return;
	}
	/**
	 *  @brief Counts the number non blank words from an array.
	 *  
	 *  @param [in] $possibleWords an array of strings or the value
	 *  	FALSE. Note that if it is FALSE the array is empty.
	 *  @endparam
	 *  @return count of non blank words
	 *  
	 *  @details counts non blank words from $possibleWords
	 */
	 protected function countOfWords($possibleWords)
	{
		if (FALSE == $possibleWords)
			return 0;
			
		$count = 0;
		foreach ($possibleWords as $word)
		{
			switch ($word)
			{
				// check for white spaces
				case CTextFileToSections::EOL_UNIX:
				case CTextFileToSections::EOL_WINDOWS:
				case CTextFileToSections::SPACE:
				case CTextFileToSections::TAB:
				case CTextFileToSections::EMPTY_STRING:
				break;
				
				default:
				$count++;
			}
		}
		return $count;
	}
	/**
	 *  @brief Extract from a line (string) all words.
	 *  @endbrief
	 *  
	 *  @param [in] $line a string of text
	 *  @returns an array of words
	 *  
	 *  @details Extract from $line all words. A word in a 
	 *  line is encased in white spaces.
	 *  @enddetails
	 */
	protected function extractWords($line)
	{
		return preg_split("/[\s]+/", 
			$line, -1,  PREG_SPLIT_DELIM_CAPTURE);
	}
	/**
	 *  @brief Checks whether a Word is a 'SECTION NAME'.
	 *  
	 *  @param [in] $word a word 
	 *  @param [in,out] $sectionName "SECTION NAME" if found
	 *  @endparam	 
	 *  @return true if this word is a  "SECTION NAME" the variable
	 *  	$sectionName is updated if found. Otherwise false is returned
	 *  	and $sectionName remains untouched.
	 *  @end return
	 *  
	 *  @details 
	 *  a section name is a word terminated with a ";" <- EOW_SECTION
	 *  @enddetails
	 */
	 protected function isASectionName($word, &$sectionName)
	{
		$sectionName = "";
		if(substr($word, -1) != CTextFileToSections::EOW_SECTION)
			return false;
		
		$sectionName = strtoupper(substr($word,0, -1));
		return $sectionName != "";
	}
/* 
 *  @if TESTROUTINES_VISIBLE 
*/
	// test routine
	function test()
	{
		$tags = array();
		$tags["TITLE"] = "";
		$tags["STORY"] = "";
		$tags["COMMENT"] = "";
		$tags["TAG"] = "";
		$this->processFile("testdata.txt", $tags);
		DEBUGVAR($tags);
	}
/* 
*   @endif
*/	
 }
 ?>