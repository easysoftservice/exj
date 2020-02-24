<?php

defined('JPATH_BASE') or die();

/**
 * SimpleXML Element
 *
 * This object stores all of the direct children of itself in the $children array.
 * They are also stored by type as arrays. So, if, for example, this tag had 2 <font>
 * tags as children, there would be a class member called $font created as an array.
 * $font[0] would be the first font tag, and $font[1] would be the second.
 *
 * To loop through all of the direct children of this object, the $children member
 *  should be used.
 *
 * To loop through all of the direct children of a specific tag for this object, it
 * is probably easier to use the arrays of the specific tag names, as explained above.
 *
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since 1.5
 */
class JSimpleXMLElement extends JObject
{
	/**
	 * Array with the attributes of this XML element
	 *
	 * @var array
	 */
	var $_attributes = array();

	/**
	 * The name of the element
	 *
	 * @var string
	 */
	var $_name = '';

	/**
	 * The data the element contains
	 *
	 * @var string
	 */
	var $_data = '';

	/**
	 * Array of references to the objects of all direct children of this XML object
	 *
	 * @var array
	 */
	var $_children = array();

	/**
	 * The level of this XML element
	 *
	 * @var int
	 */
	var $_level = 0;

	/**
	 * Constructor, sets up all the default values
	 *
	 * @param string $name
	 * @param array $attrs
	 * @param int $parents
	 * @return JSimpleXMLElement
	 */
	function __construct($name, $attrs = array(), $level = 0)
	{
		//Make the keys of the attr array lower case, and store the value
		$this->_attributes = array_change_key_case($attrs, CASE_LOWER);

		//Make the name lower case and store the value
		$this->_name = strtolower($name);

		//Set the level
		$this->_level = $level;
	}

	/**
	 * Get the name of the element
	 *
	 * @access public
	 * @return string
	 */
	function name() {
		return $this->_name;
	}

	/**
	 * Get the an attribute of the element
	 *
	 * @param string $attribute 	The name of the attribute
	 *
	 * @access public
	 * @return mixed If an attribute is given will return the attribute if it exist.
	 * 				 If no attribute is given will return the complete attributes array
	 */
	function attributes($attribute = null)
	{
		if(!isset($attribute)) {
			return $this->_attributes;
		}

		return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
	}

	/**
	 * Get the data of the element
	 *
	 * @access public
	 * @return string
	 */
	function data() {
		return $this->_data;
	}

	/**
	 * Set the data of the element
	 *
	 * @access public
	 * @param	string $data
	 * @return string
	 */
	function setData($data) {
		$this->_data = $data;
	}

	/**
	 * Get the children of the element
	 *
	 * @access public
	 * @return array
	 */
	function children() {
		return $this->_children;
	}

	/**
	 * Get the level of the element
	 *
	 * @access public
	 * @return int
	 */
	function level() {
		return $this->_level;
	}

	 /**
	 * Adds an attribute to the element
	 *
	 * @param string $name
	 * @param array  $attrs
	 */
	function addAttribute($name, $value)
	{
		//add the attribute to the element, override if it already exists
		$this->_attributes[$name] = $value;
	}

	 /**
	 * Removes an attribute from the element
	 *
	 * @param string $name
	 */
	function removeAttribute($name)
	{
		unset($this->_attributes[$name]);
	}

	/**
	 * Adds a direct child to the element
	 *
	 * @param string $name
	 * @param array  $attrs
	 * @param int 	 $level
	 * @return JSimpleXMLElement 	The added child object
	 */
	function &addChild($name, $attrs = array(), $level = null)
	{
		//If there is no array already set for the tag name being added,
		//create an empty array for it
		if(!isset($this->$name)) {
			$this->$name = array();
		}

		// set the level if not already specified
		if ($level == null)	{
			$level = ($this->_level + 1);
		}

		//Create the child object itself
		$classname = get_class( $this );
		$child = new $classname( $name, $attrs, $level );

		//Add the reference of it to the end of an array member named for the elements name
		$this->{$name}[] =& $child;

		//Add the reference to the children array member
		$this->_children[] =& $child;

		//return the new child
		return $child;
	}

	function removeChild(&$child)
	{
		$name = $child->name();
		for ($i=0,$n=count($this->_children);$i<$n;$i++)
		{
			if ($this->_children[$i] == $child) {
				unset($this->_children[$i]);
			}
		}
		for ($i=0,$n=count($this->{$name});$i<$n;$i++)
		{
			if ($this->{$name}[$i] == $child) {
				unset($this->{$name}[$i]);
			}
		}
		$this->_children = array_values($this->_children);
		$this->{$name} = array_values($this->{$name});
		unset($child);
	}

	/**
	 * Get an element in the document by / separated path
	 *
	 * @param	string	$path	The / separated path to the element
	 * @return	object	JSimpleXMLElement
	 */
	function &getElementByPath($path)
	{
		$tmp	=& $this;
		$false	= false;
		$parts	= explode('/', trim($path, '/'));

		foreach ($parts as $node)
		{
			$found = false;
			foreach ($tmp->_children as $child)
			{
				if ($child->_name == $node)
				{
					$tmp =& $child;
					$found = true;
					break;
				}
			}
			if (!$found) {
				break;
			}
		}

		if ($found) {
			$ref =& $tmp;
		} else {
			$ref =& $false;
		}
		return $ref;
	}

	/**
	 * traverses the tree calling the $callback( JSimpleXMLElement
	 * $this, mixed $args=array() ) function with each JSimpleXMLElement.
	 *
	 * @param string $callback function name
	 * @param array $args
	 */
	function map($callback, $args=array())
	{
		$callback($this, $args);
		// Map to all children
		if ($n = count($this->_children)) {
			for($i=0;$i<$n;$i++)
			{
				$this->_children[$i]->map($callback, $args);
			}
		}
	}

	/**
	 * Return a well-formed XML string based on SimpleXML element
	 *
	 * @return string
	 */
	function toString($whitespace=true)
	{
		//Start a new line, indent by the number indicated in $this->level, add a <, and add the name of the tag
		if ($whitespace) {
			$out = "\n".str_repeat("\t", $this->_level).'<'.$this->_name;
		} else {
			$out = '<'.$this->_name;
		}

		//For each attribute, add attr="value"
		foreach($this->_attributes as $attr => $value) {
			$out .= ' '.$attr.'="'.htmlspecialchars($value).'"';
		}

		//If there are no children and it contains no data, end it off with a />
		if (empty($this->_children) && empty($this->_data)) {
			$out .= " />";
		}
		else //Otherwise...
		{
			//If there are children
			if(!empty($this->_children))
			{
				//Close off the start tag
				$out .= '>';

				//For each child, call the asXML function (this will ensure that all children are added recursively)
				foreach($this->_children as $child)
					$out .= $child->toString($whitespace);

				//Add the newline and indentation to go along with the close tag
				if ($whitespace) {
					$out .= "\n".str_repeat("\t", $this->_level);
				}
			}

			//If there is data, close off the start tag and add the data
			elseif(!empty($this->_data))
				$out .= '>'.htmlspecialchars($this->_data);

			//Add the end tag
			$out .= '</'.$this->_name.'>';
		}

		//Return the final output
		return $out;
	}
}
?>