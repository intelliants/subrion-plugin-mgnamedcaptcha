<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

/**
 * Named Buttons Captcha controller
 *
 * @author ndthuan <me@ndthuan.com>
 * @package NamedButtonsCaptcha
 */

require_once dirname(__FILE__) . '/../NamedButtonsCaptcha/KeyStorage/Session.php';
require_once dirname(__FILE__) . '/../NamedButtonsCaptcha/KeyStorage/Apc.php';

class iaCaptcha extends abstractCore
{
	/**
	 * The message font size in points
	 * @var integer
	 */
	protected $_messageFontSize = 12;

	/**
	 * The button font size in points
	 * @var integer
	 */
	protected $_buttonFontSize = 12;

	/**
	 * Max width of the message in pixels
	 * @var integer
	 */
	protected $_maxMessageWidth = 500;

	/**
	 * Max height of the message in pixels
	 * @var integer
	 */
	protected $_maxMessageHeight = 40;

	/**
	 * Max width of the button in pixels
	 * @var integer
	 */
	protected $_maxButtonWidth = 50;

	/**
	 * Max height of the button in pixels
	 * @var integer
	 */
	protected $_maxButtonHeight = 40;

	/**
	 * Absolute path to the true-type font file
	 * @var string
	 */
	protected $_fontPath = 'timesbd.ttf';

	/**
	 * Background color in RGB format
	 * @var array
	 */
	protected $_backgroundColor = array(255, 255, 255);

	/**
	 * Background color in Hexadecimal format
	 * @var array
	 */
	protected $_backgroundColorHex = null;

	/**
	 * Text color in RGB format
	 * @var array
	 */
	protected $_fontColor = array(0, 0, 0);

	/**
	 * Text color in Hexadecimal format
	 * @var array
	 */
	protected $_fontColorHex = null;

	/**
	 * Number of required buttons, user must click on this number of buttons
	 * @var integer
	 */
	protected $_numberOfRequiredButtons = 3;

	/**
	 * Object used for storing key => value pairs
	 * @var NamedButtonsCaptcha_KeyStorage_Abstract
	 */
	protected $_keyStorage = null;

	/**
	 * Button labels
	 * @var array
	 */
	protected $_buttonLabels = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

	/**
	 * Button names, respective to the button labels
	 * @var array
	 */
	protected $_buttonNames = array('one', 'two', 'three', 'four', 'five',
		'six', 'seven', 'eight', 'nine', 'ten');

	/**
	 * The message template, %s would be replaced by the list of required buttons
	 * @var string
	 */
	protected $_messageTemplate = 'Please click on the numbers: %s';

	/**
	 * Absolute path to the template file for rendering the captcha
	 * @var string
	 */
	protected $_outputTemplatePath = 'template.php';

	/**
	 * List of random numbers assigned to the buttons. When user clicks on a
	 * button, the corresponding value from this list is appended to the form.
	 *
	 * @var array
	 */
	protected $_verificationValues = array();

	/**
	 * Hidden values of the buttons user clicked on must match against this list.
	 *
	 * @var array
	 */
	protected $_requiredValues = array();

	/**
	 * Indicate whether the image or plain text would be rendered
	 * @var boolean
	 */
	protected $_renderImage = true;

	/**
	 * Constructor
	 * @param array $buttons an associative array with keys are button labels
	 * and values are button names
	 */
	public function __construct($buttons = array())
	{
		// set default font path to absolute
		$this->_fontPath = dirname(__FILE__) . '/../' . $this->_fontPath;

		// set default output template file to absolute path
		$this->_outputTemplatePath = dirname(__FILE__) . '/' . $this->_outputTemplatePath;

		if (!empty($buttons))
		{
			$this->setButtons($buttons);
		}

		// trying to start session
		if (!isset($_SESSION))
		{
			session_start();
		}

		$this->setKeyStorage(new NamedButtonsCaptcha_KeyStorage_Session());
	}

	function getImage()
	{
		$html = '<div class="container">' . self::getHtml() . '</div>';

		return $html;
	}

	public function getPreview()
	{
		$html = self::getHtml();

		return $html;
	}

	function validate()
	{
		if ('POST' === $_SERVER['REQUEST_METHOD'])
		{
			$values = isset($_POST['nbc']) ? $_POST['nbc'] : array();

			if (self::isValid($values))
			{
				$_SESSION['pass'] = '';

				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Set storage of key => value pairs
	 * @param NamedButtonsCaptcha_KeyStorage_Abstract $storage
	 * @return NamedButtonsCaptcha
	 */
	public function setKeyStorage(NamedButtonsCaptcha_KeyStorage_Abstract $storage)
	{
		$this->_keyStorage = $storage;

		return $this;
	}

	/**
	 * Set buttons
	 *
	 * @param array $buttons keys are button labels, values are button names
	 * @return NamedButtonsCaptcha
	 * @throws Exception
	 */
	public function setButtons($buttons)
	{
		if (empty($buttons)) {

			throw new Exception('NamedButtonsCaptcha->setButtons(): ' .
			'Setting empty buttons array');
		}

		$this->_buttonLabels = array_keys($buttons);
		$this->_buttonNames = array_values($buttons);

		return $this;
	}

	/**
	 * Set message font size
	 * @param integer $size
	 * @return NamedButtonsCaptcha
	 */
	public function setMessageFontSize($size)
	{
		$this->_messageFontSize = $size;

		return $this;
	}

	/**
	 * Set font size of the button
	 * @param integer $size
	 * @return NamedButtonsCaptcha
	 */
	public function setButtonFontSize($size)
	{
		$this->_buttonFontSize = $size;

		return $this;
	}

	/**
	 * Set max width of the message
	 * @param integer $maxWidth
	 * @return NamedButtonsCaptcha
	 */
	public function setMaxMessageWidth($maxWidth)
	{
		$this->_maxMessageWidth = $maxWidth;

		return $this;
	}

	/**
	 * Set max height of the message
	 * @param integer $maxheight
	 * @return NamedButtonsCaptcha
	 */
	public function setMaxMessageHeight($maxheight)
	{
		$this->_maxMessageHeight = $maxheight;

		return $this;
	}

	/**
	 * Set max width of the button
	 * @param integer $maxWidth
	 * @return NamedButtonsCaptcha
	 */
	public function setMaxButtonWidth($maxWidth)
	{
		$this->_maxButtonWidth = $maxWidth;

		return $this;
	}

	/**
	 * Set max height of the button
	 * @param integer $maxheight
	 * @return NamedButtonsCaptcha
	 */
	public function setMaxButtonHeight($maxheight)
	{
		$this->_maxButtonHeight = $maxheight;

		return $this;
	}

	/**
	 * Set font path
	 * @param string $fontPath absolute path to the true-type font file
	 * @return NamedButtonsCaptcha
	 */
	public function setFontPath($fontPath)
	{
		$this->_fontPath = $fontPath;

		return $this;
	}

	/**
	 * Set font color
	 * @param array list of RGB colors
	 * @return NamedButtonsCaptcha
	 */
	public function setFontColor(array $rgbArray)
	{
		$this->_fontColor = $rgbArray;

		return $this;
	}

	/**
	 * Set background color
	 * @param array list of RGB colors
	 * @return NamedButtonsCaptcha
	 */
	public function setBackgroundColor(array $rgbArray)
	{
		$this->_backgroundColor = $rgbArray;

		return $this;
	}

	/**
	 * Set output template file
	 * @param string $file absolute path to the output template file
	 * @return NamedButtonsCaptcha
	 */
	public function setOutputTemplatePath($file)
	{
		$this->_outputTemplatePath = $file;

		return $this;
	}

	/**
	 * Set message template
	 * @param string $messageTemplate the message must contain the %s
	 * @return NamedButtonsCaptcha
	 */
	public function setMessageTemplate($messageTemplate)
	{
		$this->_messageTemplate = $messageTemplate;

		return $this;
	}

	/**
	 * Firstly, try to create a very large image that can contain a long string
	 * then auto-trim the whitespace. This helps produce an image that can fit
	 * the text without setting the font size correctly.
	 *
	 * @see http://stackoverflow.com/questions/1669683/crop-whitespace-from-image-in-php
	 *
	 * @param string $text
	 * @param integer $fontSize font size
	 * @param integer $maxWidth max width of the image
	 * @param integer $maxheight max height of the image
	 * @return string base64-based string containing data of the image
	 */
	public function createBase64ImageFromText($text, $fontSize, $maxWidth = 350, $maxHeight = 40)
	{
		// Create the image
		$im = imagecreatetruecolor($maxWidth, $maxHeight);

		$backgroundColorHex = $this->getBackgroundColorHex();

		// Create some colors
		$backgroundColor = imagecolorallocate($im, $this->_backgroundColor[0],
			$this->_backgroundColor[1], $this->_backgroundColor[2]);
		$fontColor = imagecolorallocate($im, $this->_fontColor[0],
			$this->_fontColor[1], $this->_fontColor[2]);
		imagefilledrectangle($im, 0, 0, $maxWidth, $maxHeight, $backgroundColor);

		// Add the text
		if (file_exists($this->_fontPath)) {
			imagettftext($im, $fontSize, 0, 1, $fontSize + 1, $fontColor, $this->_fontPath, $text);
		} else {
			imagestring($im, 5, 0, 0, $text, $fontColor);
		}

		// start trimming
		//find the size of the borders
		$b_top = 0;
		$b_btm = 0;
		$b_lft = 0;
		$b_rt = 0;

		//top
		for (; $b_top < imagesy($im); ++$b_top)
		{
			for ($x = 0; $x < imagesx($im); ++$x)
			{
				if (imagecolorat($im, $x, $b_top) != $backgroundColorHex) {
					break 2; //out of the 'top' loop
				}
			}
		}

		//bottom
		for (; $b_btm < imagesy($im); ++$b_btm)
		{
			for ($x = 0; $x < imagesx($im); ++$x)
			{
				if (imagecolorat($im, $x, imagesy($im) - $b_btm - 1) != $backgroundColorHex) {
					break 2; //out of the 'bottom' loop
				}
			}
		}

		//left
		for (; $b_lft < imagesx($im); ++$b_lft)
		{
			for ($y = 0; $y < imagesy($im); ++$y)
			{
				if (imagecolorat($im, $b_lft, $y) != $backgroundColorHex) {
					break 2; //out of the 'left' loop
				}
			}
		}

		//right
		for (; $b_rt < imagesx($im); ++$b_rt)
		{
			for ($y = 0; $y < imagesy($im); ++$y)
			{
				if (imagecolorat($im, imagesx($im) - $b_rt - 1, $y) != $backgroundColorHex) {
					break 2; //out of the 'right' loop
				}
			}
		}

		//copy the contents, excluding the border
		$newimg = imagecreatetruecolor(
			imagesx($im) - ($b_lft + $b_rt), imagesy($im) - ($b_top + $b_btm));

		imagecopy($newimg, $im, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));

		// Using imagepng() results in clearer text compared with imagejpeg()
		ob_start();
		imagepng($newimg);
		$imgBinary = ob_get_clean();

		imagedestroy($newimg);
		imagedestroy($im);

		$imgData = base64_encode($imgBinary);

		return $imgData;
	}

	/**
	 * Set number of required buttons
	 * @param integer $num
	 * @return NamedButtonsCaptcha
	 * @throws Exception
	 */
	public function setNumberOfRequiredButtons($num)
	{
		if ($num < 1) {

			throw new Exception('NamedButtonsCaptcha->setNumberOfRequiredButtons(): '
			. 'Number of required buttons cannot be smaller than 1');
		}

		$this->_numberOfRequiredButtons = $num;

		return $this;
	}

	/**
	 * Set to use image or not
	 * @param boolean $renderImage
	 * @return NamedButtonsCaptcha
	 */
	public function setRenderImage($renderImage = true)
	{
		$this->_renderImage = $renderImage;

		return $this;
	}

	/**
	 * Render the captcha. This will generate random numbers to assign to the
	 * buttons. To correctly verify the inputs, isValid() MUST be called before
	 * this method.
	 */
	public function render()
	{
		// prepare random numbers for buttons
		$this->_prepareVerificationValues();

		$buttons = array();

		// get random indexes of the required buttons
		$randomKeys = array_rand($this->_buttonLabels, $this->_numberOfRequiredButtons);

		if (1 == $this->_numberOfRequiredButtons) {
			$randomKeys = array($randomKeys);
		}

		// list of required buttons appeared as text
		$requiredNumbersText = array();

		foreach ($randomKeys as $randomKey)
		{
			$requiredNumbersText[] = $this->_buttonNames[$randomKey];
			$this->_requiredValues[] = $this->_verificationValues[$randomKey];
		}

		// shuffle required buttons
		shuffle($requiredNumbersText);

		$this->_keyStorage->write('requiredValues', $this->_requiredValues);

		// get the message
		$message = sprintf($this->_messageTemplate, implode(', ', $requiredNumbersText));

		if ($this->_renderImage) {
			// address to message image
			$message = '<img src="data:image/png;base64,' . $this->createBase64ImageFromText($message, $this->_messageFontSize, $this->_maxMessageWidth, $this->_maxMessageHeight) . '" style="height: auto;" />';

			// render the button images
			foreach ($this->_buttonLabels as $index => $buttonLabel)
			{
				$buttons[$this->_verificationValues[$index]] = '<img src="data:image/png;base64,'
					. $this->createBase64ImageFromText($buttonLabel, $this->_buttonFontSize, $this->_maxButtonWidth, $this->_maxButtonHeight) . '" style="height: auto;"/>';
			}

		} else {

			// render the button images
			foreach ($this->_buttonLabels as $index => $buttonLabel)
			{
				$buttons[$this->_verificationValues[$index]] = $buttonLabel;
			}

		}

		// shuffle the button images
		$buttons = self::_shuffleAssociativeArray($buttons);

		// display the results
		require $this->_outputTemplatePath;
	}

	/**
	 * Get HTML output of the captcha
	 *
	 * @return string
	 */
	public function getHtml()
	{
		ob_start();

		$this->render();

		return ob_get_clean();
	}

	/**
	 * Get background color in hex format
	 * @return integer
	 */
	public function getBackgroundColorHex()
	{
		if (null === $this->_backgroundColorHex) {
			$red = dechex($this->_backgroundColor[0]);
			$green = dechex($this->_backgroundColor[1]);
			$blue = dechex($this->_backgroundColor[2]);

			$this->_backgroundColorHex = eval("return 0x{$red}{$green}{$blue};");
		}

		return $this->_backgroundColorHex;
	}

	/**
	 * Get background color in hex format
	 * @return integer
	 */
	public function getFontColorHex()
	{
		if (null === $this->_fontColorHex) {
			$red = dechex($this->_fontColor[0]);
			$green = dechex($this->_fontColor[1]);
			$blue = dechex($this->_fontColor[2]);

			$this->_fontColorHex = eval("return 0x{$red}{$green}{$blue};");
		}

		return $this->_fontColorHex;
	}

	/**
	 * Validate the submitted values, this must be called before rendering
	 * @param array $submittedValues
	 * @return bolean
	 */
	public function isValid(array $submittedValues)
	{
		$requiredValues = (array) $this->_keyStorage->read('requiredValues');

		$diff = array_diff($submittedValues, $requiredValues);
		$diff2 = array_diff($requiredValues, $submittedValues);

		return empty($diff) && empty($diff2);
	}

	/**
	 * Create a list of random numbers that correspond to the buttons
	 */
	protected function _prepareVerificationValues()
	{
		$verificationValues = array();

		$numButtons = count($this->_buttonLabels);

		for ($counter = 1; $counter <= $numButtons; ++$counter)
		{
			$verificationValues[] = rand(10000000, 999999999);
		}

		$this->_verificationValues = $verificationValues;
	}

	/**
	 * Shuffle an associative array preserving the keys
	 *
	 * @param array $array
	 * @return array
	 */
	protected static function _shuffleAssociativeArray(array $array)
	{
		// get the keys
		$keys = array_keys($array);
		$newArray = array();

		// shuffle the keys
		shuffle($keys);

		// copy to the new array
		foreach ($keys as $key)
		{
			$newArray[$key] = $array[$key];
		}

		return $newArray;
	}
}