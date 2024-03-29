<?php

/**
 *  An easy width/height manipulations with preservation of aspect ratio
 *  
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 OpenBuildings Inc.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 * @since  28.12.2008
 */
class Aspect {

	public static function factory($width, $height)
	{
		return new Aspect($width, $height);
	}
	
	protected $_width = 0;
	protected $_height = 0;
	
	protected $_x = 0;
	protected $_y = 0;
	
	protected $_ratio = 0;
	
	/**
	 *	After it's created with given width/height the aspect object preserves their ratio, so that for example if you modify the width,
	 *	the height will be changed accordingly. This also allows for more complex manipulation - as centering and cropping
	 *	
	 */
	function __construct($width, $height) 
	{
		if ( ! $width OR ! $height)
			throw new Kohana_Exception("Aspect must have width and height bigger than zero, was (:width, :height)", array(':width' => $width, ':height' => $height));

		$this->_width = $width;
		$this->_height = $height;
		
		$this->_ratio = $width / $height;
	}
	
	/**
	 * Set Width, preserving aspect ratio, or get the current width
	 * @param  integer $new_width 
	 * @return integer|Aspect width | $this
	 */
	public function width($new_width = NULL)
	{
		if ($new_width !== NULL)
		{
			$this->_height = $new_width / $this->ratio();
			$this->_width = $new_width;
			return $this;
		}

		return $this->_width;
	}

	/**
	 * Set Height, preserving aspect ratio, or get the current width
	 * @param  integer $new_height 
	 * @return integer|Aspect height | $this
	 */
	public function height($new_height = NULL)
	{
		if ($new_height !== NULL)
		{
			$this->_width = $new_height * $this->ratio();
			$this->_height = $new_height;
		}

		return $this->_height;
	}

	/**
	 * Modify x and y 
	 * @param  integer $x 
	 * @param  integer $y 
	 * @return Aspect    $this
	 */
	public function offset($x, $y)
	{
		$this->_x += $x;
		$this->_y += $y;
		
		return $this;
	}

	/**
	 * Getter x
	 * @return integer 
	 */
	public function x()
	{ 
		return $this->_x;
	}

	/**
	 * Getter y
	 * @return integer 
	 */
	public function y()
	{
		return $this->_y;
	}
	
	/**
	 * Getter aspect ratio (width / height)
	 * @return integer
	 */
	public function ratio()
	{
		return $this->_ratio;
	}

	/**
	 * Check if the image is portrait (width < height)
	 * @return boolean
	 */
	public function is_portrait()
	{
		return $this->_ratio < 1;
	}

	/**
	 * Check if the image is landscape (height >= width)
	 * @return boolean 
	 */
	public function is_landscape()
	{
		return $this->_ratio >= 1;
	}

	/**
	 * get x + width or set x relative to the right border
	 * @param  integer $value 
	 * @return integer|Aspect        
	 */
	public function right($value = NULL) 
	{ 
		if ($value !== NULL)
		{
			$this->_x = $value - $this->_width;
			return $this;
		}

		return $this->_x + $this->_width;
	}
	
	/**
	 * get y + height or set y relative to the bottom border
	 * @param  integer $value 
	 * @return integer|Aspect        
	 */
	public function bottom($value = NULL) 
	{ 
		if ($value !== NULL)
		{
			$this->_y = $value - $this->_height;
			return $this;
		}

		return $this->_y + $this->_height; 
	}

	/**
	 * Return an array (width, height)
	 * @return array
	 */
	public function dims()
	{
		return array($this->_width, $this->_height);
	}
	
	/**
	 * Return an array (x, y)
	 * @return array 
	 */
	public function pos()
	{
		return array($this->_x, $this->_y);
	}
	
	/**
	 * The new height/width will be the largest posible to fit the given width/height, without going out of those dimensions
	 * @return Aspect
	 */
	public function constrain($width, $height)
	{
		$this->_x = $this->_y = 0;

		if (($width / $height) < $this->ratio()) 
		{
			$this->width($width);
			$this->_y = ($height - $this->_height) / 2.0;
		}
		else 
		{
			$this->height($height);
			$this->_x = ($width - $this->_width) / 2.0;
		}

		return $this;
	}
	
	/**
	 * Add to the widht and the height, keeping the image in place (reducing x and y)
	 *	@return Aspect
	 */
	public function inflate($width, $height)
	{
		$this->_width += $width;
		$this->_height += $height;
		$this->_x -= $width / 2;
		$this->_y -= $height / 2;
		
		return $this;
		
	}
	
	/**
	 *	The width and height will become the smallest possible so that they fully enclose those dimensions
	 *	@return		Aspect
	 */
	public function crop($width, $height)
	{
		$this->_x = $this->_y = 0;

		if (($width / $height) > $this->ratio()) 
		{
			$this->width($width);
			$this->_y = ($height - $this->_height) / 2.0;
		}
		else 
		{
			$this->height($height);
			$this->_x = ($width - $this->_width) / 2.0;
		}
		
		return $this;
	}

	/**
	 *	@return		Aspect
	 */
	public function center($width, $height)
	{
		return $this->relative($width, $height, 0.5, 0.5);
	}
	
	/**
	 * Position an element in a relative position of the parent
	 *	@return		Aspect
	 */
	public function relative($width, $height, $x_part, $y_part)
	{
		$this->_x = (($width - $this->_width) * $x_part);
		$this->_y = (($height - $this->_height) * $y_part);

		return $this;
	}

	public function as_array()
	{
		return array(
			'width' => $this->_width,
			'height' => $this->_height,
			'x' => $this->_x,
			'y' => $this->_y,
		);
	}
	
	/**
	 * Round all variables (x, y, width, height)
	 * @return Aspect
	 */
	public function round_all()
	{
		$this->_x = round($this->_x);
		$this->_y = round($this->_y);
		$this->_width = round($this->_width);
		$this->_height = round($this->_height);

		return $this;
	}
	
	public function __toString()
	{
		return "[Aspect] { x: ".$this->_x.", y:".$this->_y.",  width:".$this->_width.", height:".$this->_height." }";
	}
}