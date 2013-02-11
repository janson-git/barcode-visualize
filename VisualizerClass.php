<?php

class VisualizerClass
{
	private $minValueToDisplay;
	private $maxValueToDisplay;
	
	private $colorGradientImage;
	private $scaleColors;
	private $data = array();
	
	private $resultImageWidth  = 500;
	private $resultImageHeight = 50;
	private $resultImageBackgroundColor = '#FFFFFF';
	private $resultImageName   = 'default_name';
	
	private $resultImageResource;
	
	public function __construct() {}
	
	public function setMinValueToDisplay($value)
	{
		$this->minValueToDisplay = $value;
	}
	public function setMaxValueToDisplay($value)
	{
		$this->maxValueToDisplay = $value;
	}

	/**
	 * Set path to image, that be used as color scale
	 * @param string $scaleImageFilename
	 */
	public function setColorGradientImage($scaleImageFilename)
	{
		$this->colorGradientImage = $scaleImageFilename;
	}

	/**
	 * Set data to visualize
	 * @param array $data
	 */
	public function setDataArray($data = array())
	{
		$this->data = $data;
	}
	
	public function setBackgroundColor($color = '#FFFFFF')
	{
		$this->resultImageBackgroundColor = $color;
	}
	
	public function setResultImageName($name)
	{
		$this->resultImageName = $name;
	}
	
	public function setResultImageWidth($width)
	{
		$this->resultImageWidth = $width;
	}
	public function setResultImageHeight($height)
	{
		$this->resultImageHeight = $height;
	}
	
	
	public function createImage()
	{
		$this->resultImageResource = imagecreatetruecolor($this->resultImageWidth, $this->resultImageHeight);
		
		// FILL IMAGE WITH BACKGROUND COLOR
		$backgroundColor = $this->getColorFromString($this->resultImageBackgroundColor);
		for ($i = 0; $i <= $this->resultImageWidth; $i++) {
			imageline($this->resultImageResource, $i, 0, $i, $this->resultImageHeight, $backgroundColor);
		}
		imagealphablending($this->resultImageResource, true);
		
		// GET COLORS PALETTE FROM SCALE SOURCE IMAGE
		$this->parseColorsFromScaleImage();
		
		// GET MAX counts from data to properly map it to colors scale
		$maxCount = 1;
		foreach ($this->data as $item) {
			$count = isset($item['count']) ? $item['count'] : 1;
			if ($count > $maxCount) {
				$maxCount = $count;
			}
		}

		// check for data range, maybe need to recalculate scale
		$values   = $this->maxValueToDisplay - $this->minValueToDisplay;
		$stepSize = $this->resultImageWidth / $values;
		$onePercentValue = $values / 100;

		foreach ($this->data as $item) {
			$val      = $item['val'];
			$valCount = isset($item['count']) ? $item['count'] : 1;
			if ($val < $this->minValueToDisplay || $val > $this->maxValueToDisplay) {
				continue;
			}
			
			// if need - recalculate
			if ($values < $this->resultImageWidth) {
				// COUNT PERCENTS
				$currentPercent = (($val - $this->minValueToDisplay) / $values) * 100;
				$imagePosition  = $onePercentValue * $currentPercent * $stepSize;
			} else {
			// or just display result as is
				$imagePosition = $val - $this->minValueToDisplay;
			}
			
			$x = $imagePosition;
			$color = $this->scaleColors[ intval(($valCount / $maxCount) * 100) ];
			if ($stepSize > 0) {
				for ($i = 0; $i < $stepSize; $i++) {
					imageline($this->resultImageResource, $x+$i, 0, $x+$i, $this->resultImageHeight, $color);
				}
			} else {
				imageline($this->resultImageResource, $x, 0, $x, $this->resultImageHeight, $color);
			}
		}
	}

	/**
	 * @return bool
	 */
	public function drawImage()
	{
		return imagepng($this->resultImageResource);
	}
	
	public function saveImage($path = '')
	{
		if (!empty($path)) {
			rtrim($path, '/\\');
			$path .= '/';
		}
		imagepng($this->resultImageResource, "{$path}{$this->resultImageName}.png");
	}


	/**
	 * Parse imagecolorallocate result from string value of color.
	 * Color string is '#RRGGBB' or '#RGB' as css colors value.
	 * @param $colorString
	 * @return int
	 * @throws UnexpectedValueException
	 */
	private function getColorFromString($colorString)
	{
		$colorString = str_replace('#', '', $colorString);
		$is_correct  = preg_match('#[0-9a-fA-F]#', $colorString);
		$strlen      = strlen($colorString);
		if (!$is_correct || ($strlen != 3 && $strlen != 6)) {
			throw new UnexpectedValueException('Wrong value of background color');
		}
		
		if (strlen($colorString) == 6) {
			$colors = str_split($colorString, 2);
		} elseif (strlen($colorString) == 3) {
			$colors = str_split($colorString);
			foreach ($colors as &$color) {
				$color .= $color;
			}
			unset($color);
		}
		
		foreach ($colors as &$color) {
			$color = hexdec($color);
		}
		unset($color);
		
		return imagecolorallocate($this->resultImageResource, $colors[0], $colors[1], $colors[2]);
	}

	/**
	 * Parse color scale from assigned color gradient image.
	 * It will get 100 color samples (from 0 to 100 percents) by width of image.
	 * Image can be jpg, png or gif image.
	 */
	private function parseColorsFromScaleImage()
	{
		$scaleSize = getimagesize($this->colorGradientImage);
		
		switch ($scaleSize['mime']) {
			case 'image/jpeg':
				$scaleImg = imagecreatefromjpeg($this->colorGradientImage);
				break;
			case 'image/gif':
				$scaleImg = imagecreatefromgif($this->colorGradientImage);
				break;
			default:
				$scaleImg = imagecreatefrompng($this->colorGradientImage);
		}

		$imageWidth = $scaleSize[0];
		$scaleStep  = $imageWidth / 100;

		$this->scaleColors = array();
		for ($i = 0; $i <= 100; $i++) {
			$x = $scaleStep * $i;
			if ($x >= $imageWidth) {
				$x = $imageWidth - 1;
			}
			$this->scaleColors[$i] = imagecolorat($scaleImg, $x, 0);
		}
	}
	
}
