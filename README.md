Images Layout and Composition Module
====================================

This module handles composing images in arbitrary way on a static (or extendable) canvas.

Basically you must create layout classes that extend Image_Layout and implements the calculate() method. After that you can use those layouts to position images (using html position aboslute and background:clip() properties)


class Image_Aspect
------------------

This class holds the positioning information of a given image. It has two consepts - the "canvas" and the "image". The canvas is the enclosing element, and holds the absolute position of the image. The image is "clipped" by the canvas (using background:clip() css)

	                   Canvas
	  ┌──────────────┐⤴
	░░│░░░░░░░░░░░░░░│░░
	░░│░░░░░░░░░░░░░░│░░
	░░│░░░░░░░░░░░░░░│░░
	░░│░░░░░░░░░░░░░░│░░⤵ 
	  └──────────────┘   Image

This combination of resizing and croping allows for any image arangement desired. You can even extend Image_Aspect to add rotaiton or 3d stuff.


__The canvas:__

You can position and resize the canvas with `canvas_x()`,`canvas_y()`,`canvas_width()`,`canvas_height()`. 
You can use `canvas_pos($x, $y)` to change x and y at once, and `canvas_dims($width, $height)` to change width and height at once. 
You can also use `canvas_center($width, $height)` to center the canvas inside a given rectangle


__The image:__

The image has a wide array of positioning methods that modify its position and size, relative to the its canvas.

* `x()` Get x
* `y()` Get y
* `offset($x, $y)` - Modify x and y
* `pos($x, $y)` - Get or set x and y
* `center($width, $height)` - Change the x or y to center the image inside the given rectangle
* `width()` Get or set the width, keeping the aspect ratio
* `height()` Get or set the height, keeping the aspect ratio
* `dims($width, $height)` - Get or set width and height
* `ratio()` Get the aspect ratio.
* `is_portrait()` Find out if its a portrait image
* `is_landscape()` Find out if its a landscape image
* `rigtht()` Get the right border of the image, or set the position x to it
* `bottom()` Get the bottom border of the image, or set the position y to it
* `inflate($width, $height)` Add to the width or height of the image, keeping it in place
* `constrain($width, $height)` Constrain the image inside the provided rectangle, keeping the aspect ratio
* `crop($width, $height)` Constrain the image inside the provided rectangle, keeping the aspect ratio, and so that it fills the whole rectangle, cropping it



class Image_Layout
------------------

You create your own layouts that position images in a certain way. 
First you'll have to set the width / height of the layout. Then you position the image aspects where you desire.

	<?php

	class Image_Layout_Folder extends Image_Layout
	{
		const THUMB_WIDTH = 150;
		const THUMB_HEIGHT = 100;

		function calculate() 
		{
			// Convert image Image_Aspect from Jam Models
			$this->_images = Image_Layout::aspect_from_model_array($this->_images, $this->field());

			// Place images in 2 columns cropped 150x100 
			foreach ($this->_images as $i => $image)
			{
				$image
					->canvas_pos(($i %2) * self::THUMB_WIDTH, floor($i / 2) * self::THUMB_HEIGHT)
					->canvas_dims(self::THUMB_WIDTH, self::THUMB_HEIGHT)
					->crop(self::THUMB_WIDTH, self::THUMB_HEIGHT);
			}

			// Set the layout with / height
			$this->_height = ceil(count($this->_images) / 2);
			$this->_width  = self::THUMB_WIDTH * 2;
		}
	}


Usage:
------

	$layout = Image_Layout::factory('folder', $images);


	//HTML Template

	<ul style="<?php echo $layout->style(); ?>">
		<?php foreach ($layout->aspect() as $aspect): ?>
			<li style="<?php echo $aspect->canvas_style() ?>">
				<img style="<?php echo $aspect->style() ?>" src="<?php echo $images[$i]->file->url() />" />
			</li>
		<?php endforeach; ?>
	</ul>

Different types of images:
--------------------------

Sometimes you want to have images that are of different status, for example cover images you can add them with array keys:

	$layout = Image_Layout::factory('folder', array('cover_image' => $image, 'images' => $images));

And you can access those images later using

	$layout->aspect('cover_image'); // Cover image
	$layout->aspect('images');      // Images array
	$layout->aspect('images.0');    // First image


--------------------------
Creator: Ivan Kerin

