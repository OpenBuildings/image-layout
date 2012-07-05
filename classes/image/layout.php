<?php
/**
* Image_Layout class is allows you to get the width/height and position of a collection of images given a selected layout
* as well as finding out which layout is most appropriate.
*/
abstract class Image_Layout
{
	public static function factory($type, array $images = array())
	{
		$class = 'Image_Layout_'.$type;

		if ( ! class_exists($class))
			throw new Kohana_Exception('Image layout :type does not exist (class :class)', array(':type' => $type, ':class' => $class));

		return new $class($images);
	}

	/**
	 * Get array('width' => ..., 'height' => ...) from an array of Jam models
	 * @param  array|Jam_Collection $collection
	 * @param  string $field      the field for the image
	 * @return array
	 */
	public static function aspect_from_model($model, $field = 'file')
	{
		if ($model instanceof Image_Aspect)
			return $model;

		if ( ! $model->meta()->field($field))
			throw new Kohana_Exception('Model ":model" does not have a field ":field"', array(':model' => $model->meta()->model(), ':field' => $field));

		$dims = $model->$field->dimensions();
	
		return Image_Aspect::factory($dims['width'], $dims['height']);
	}

	public static function aspect_from_model_array($models, $field = 'file')
	{
		$processed_thumbnails = array();
		foreach ($models as $i => $thumb) 
		{
			if (is_array($thumb) OR $thumb instanceof Jam_Collection)
			{
				$processed_thumbnails[$i] = Image_Layout::aspect_from_model_array($thumb, $field);	
			}
			else
			{
				$processed_thumbnails[$i] = Image_Layout::aspect_from_model($thumb, $field);
			}
		}
		return $processed_thumbnails;
	}

	protected $_width = 0;
	protected $_height = 0;
	protected $_images = array();
	protected $_field = 'file';
	protected $_calculated = FALSE;

	public function __construct(array $images = array())
	{
		$this->_images = $images;
	}

	abstract public function calculate();

	/**
	 * get the aspect of an image or group of images
	 * @param  string $group the name of the image / group
	 * @return Aspect        
	 */
	public function aspect($group = NULL)
	{
		if ( ! $this->_calculated)
		{
			$this->calculate();
			$this->_calculated = TRUE;
		}

		if ( ! $group)
			return $this->_images;

		return Arr::path($this->_images, $group);
	}

	public function width($width = NULL)
	{
		if ($width !== NULL)
		{
			$this->_width = $width;
			return $this;
		}
		return $this->_width;
	}

	public function height($height = NULL)
	{
		if ($height !== NULL)
		{
			$this->_height = $height;
			return $this;
		}
		return $this->_height;
	}

	public function field($field = NULL)
	{
		if ($field !== NULL)
		{
			$this->_field = $field;
			return $this;
		}
		return $this->_field;
	}

	public function style($unit = 'px')
	{
		if ( ! $this->_calculated)
		{
			$this->calculate();
			$this->_calculated = TRUE;
		}

		return join('; ', array(
			'width: '.Image_Aspect::to_css_style($this->width(), $unit),
			'height: '.Image_Aspect::to_css_style($this->height(), $unit),
		));
	}
}