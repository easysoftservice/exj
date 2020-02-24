<?php
/**
* @version		$Id: simplexml.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla.Framework
* @subpackage	Utilities
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * SimpleXML implementation.
 *
 * The XML Parser extension (expat) is required to use JSimpleXML.
 *
 * The class provides a pure PHP4 implementation of the PHP5
 * interface SimpleXML. As with PHP5's SimpleXML it is what it says:
 * simple. Nevertheless, it is an easy way to deal with XML data,
 * especially for read only access.
 *
 * Because it's not possible to use the PHP5 ArrayIterator interface
 * with PHP4 there are some differences between this implementation
 * and that of PHP5:
 *
 * <ul>
 * <li>The access to the root node has to be explicit in
 * JSimpleXML, not implicit as with PHP5. Write
 * $xml->document->node instead of $xml->node</li>
 * <li>You cannot acces CDATA using array syntax. Use the method data() instead</li>
 * <li>You cannot access attributes directly with array syntax. use attributes()
 * to read them.</li>
 * <li>Comments are ignored.</li>
 * <li>Last and least, this is not as fast as PHP5 SimpleXML--it is pure PHP4.</li>
 * </ul>
 *
 * Example:
 * <code>
 * :simple.xml:
 * <?xml version="1.0" encoding="utf-8" standalone="yes"?>
 * <document>
 *   <node>
 *	 <child gender="m">Tom Foo</child>
 *	 <child gender="f">Tamara Bar</child>
 *   <node>
 * </document>
 *
 * ---
 *
 * // read and write a document
 * $xml = new JSimpleXML;
 * $xml->loadFile('simple.xml');
 * print $xml->document->toString();
 *
 * // access a given node's CDATA
 * print $xml->root->node->child[0]->data(); // Tom Foo
 *
 * // access attributes
 * $attr = $xml->root->node->child[1]->attributes();
 * print $attr['gender']; // f
 *
 * // access children
 * foreach( $xml->root->node->children() as $child ) {
 *   print $child->data();
 * }
 * </code>
 *
 * Note: JSimpleXML cannot be used to access sophisticated XML doctypes
 * using datatype ANY (e.g. XHTML). With a DOM implementation you can
 * handle this.
 *
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since 1.5
 */
class JSimpleXML extends JObject
{
	/**
	 * The XML parser
	 *
	 * @var resource
	 */
	var $_parser = null;

	/**
	* The XML document
	*
	* @var string
	*/
	var $_xml = '';

	/**
	* Document element
	*
	* @var object
	*/
	var $document = null;

	/**
	* Current object depth
	*
	* @var array
	*/
	var $_stack = array();


	/**
	 * Constructor.
	 *
	 * @access protected
	 */
	function __construct($options = null)
	{
		if(! function_exists('xml_parser_create')) {
			return false; //TODO throw warning
		}

		//Create the parser resource and make sure both versions of PHP autodetect the format.
		$this->_parser = xml_parser_create('');

		// check parser resource
		xml_set_object($this->_parser, $this);
		xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
		if( is_array($options) )
		{
			foreach( $options as $option => $value ) {
				xml_parser_set_option($this->_parser, $option, $value);
			}
		}

		//Set the handlers
		xml_set_element_handler($this->_parser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->_parser, '_characterData');
	}

	 /**
	 * Interprets a string of XML into an object
	 *
	 * This function will take the well-formed xml string data and return an object of class
	 * JSimpleXMLElement with properties containing the data held within the xml document.
	 * If any errors occur, it returns FALSE.
	 *
	 * @param string  Well-formed xml string data
	 * @param string  currently ignored
	 * @return object JSimpleXMLElement
	 */
	function loadString($string, $classname = null) {
		$this->_parse($string);
		return true;
	}

	 /**
	 * Interprets an XML file into an object
	 *
	 * This function will convert the well-formed XML document in the file specified by filename
	 * to an object  of class JSimpleXMLElement. If any errors occur during file access or
	 * interpretation, the function returns FALSE.
	 *
	 * @param string  Path to xml file containing a well-formed XML document
	 * @param string  currently ignored
	 * @return boolean True if successful, false if file empty
	 */
	function loadFile($path, $classname = null)
	{
		//Check to see of the path exists
		if ( !file_exists( $path ) )  {
			return false;
		}

		//Get the XML document loaded into a variable
		$xml = trim( file_get_contents($path) );
		if ($xml == '')
		{
			return false;
		}
		else
		{
			$this->_parse($xml);
			return true;
		}
	}

	/**
	 * Get a JSimpleXMLElement object from a DOM node.
	 *
	 * This function takes a node of a DOM  document and makes it into a JSimpleXML node.
	 * This new object can then be used as a native JSimpleXML element. If any errors occur,
	 * it returns FALSE.
	 *
	 * @param string	DOM  document
	 * @param string   	currently ignored
	 * @return object 	JSimpleXMLElement
	 */
	function importDOM($node, $classname = null) {
		return false;
	}

	/**
	 * Get the parser
	 *
	 * @access public
	 * @return resource XML parser resource handle
	 */
	function getParser() {
		return $this->_parser;
	}

	/**
	 * Set the parser
	 *
	 * @access public
	 * @param resource	XML parser resource handle
	 */
	function setParser($parser) {
		$this->_parser = $parser;
	}

	/**
	 * Start parsing an XML document
	 *
	 * Parses an XML document. The handlers for the configured events are called as many times as necessary.
	 *
	 * @param $xml 	string 	data to parse
	 */
	function _parse($data = '')
	{
		//Error handling
		if (!xml_parse($this->_parser, $data)) {
			$this->_handleError(
				xml_get_error_code($this->_parser),
				xml_get_current_line_number($this->_parser),
				xml_get_current_column_number($this->_parser)
			);
		}

		//Free the parser
		xml_parser_free($this->_parser);
	}

	/**
	 * Handles an XML parsing error
	 *
	 * @access protected
	 * @param int $code XML Error Code
	 * @param int $line Line on which the error happened
	 * @param int $col Column on which the error happened
	 */
	function _handleError($code, $line, $col)
	{
		JError::raiseWarning( 'SOME_ERROR_CODE' , 'XML Parsing Error at '.$line.':'.$col.'. Error '.$code.': '.xml_error_string($code));
	}

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return object
	 */
	function _getStackLocation()
	{
		$return = '';
		foreach($this->_stack as $stack) {
			$return .= $stack.'->';
		}

		return rtrim($return, '->');
	}

	/**
	 * Handler function for the start of a tag
	 *
	 * @access protected
	 * @param resource $parser
	 * @param string $name
	 * @param array $attrs
	 */
	function _startElement($parser, $name, $attrs = array())
	{
		//Check to see if tag is root-level
		$count = count($this->_stack);
		if ($count == 0)
		{
			//If so, set the document as the current tag
			$classname = get_class( $this ) . 'Element';
			$this->document = new $classname($name, $attrs);

			//And start out the stack with the document tag
			$this->_stack = array('document');
		}
		//If it isn't root level, use the stack to find the parent
		else
		{
			 //Get the name which points to the current direct parent, relative to $this
			$parent = $this->_getStackLocation();

			//Add the child
			eval('$this->'.$parent.'->addChild($name, $attrs, '.$count.');');

			//Update the stack
			eval('$this->_stack[] = $name.\'[\'.(count($this->'.$parent.'->'.$name.') - 1).\']\';');
		}
	}

	/**
	 * Handler function for the end of a tag
	 *
	 * @access protected
	 * @param resource $parser
	 * @param string $name
	 */
	function _endElement($parser, $name)
	{
		//Update stack by removing the end value from it as the parent
		array_pop($this->_stack);
	}

	/**
	 * Handler function for the character data within a tag
	 *
	 * @access protected
	 * @param resource $parser
	 * @param string $data
	 */
	function _characterData($parser, $data)
	{
		//Get the reference to the current parent object
		$tag = $this->_getStackLocation();

		//Assign data to it
		eval('$this->'.$tag.'->_data .= $data;');
	}
}