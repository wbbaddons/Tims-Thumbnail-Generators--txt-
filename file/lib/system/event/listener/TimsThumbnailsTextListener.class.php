<?php
namespace wcf\system\event\listener;

/**
 * Generates thumbnails for text-files.
 *
 * @author 	Tim Düsterhus
 * @copyright	2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.thumbnailGenerators.txt
 * @subpackage	system.event.listener
 */
class TimsThumbnailsTextListener implements \wcf\system\event\IEventListener {
	private $eventObj = null;
	
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$this->eventObj = $eventObj;
		switch ($eventName) {
			case 'checkThumbnail':
			case 'generateThumbnail':
				$this->$eventName();
			default:
				return;
		}
	}
	
	/**
	 * Registers the files for thumbnail-creation
	 */
	public function checkThumbnail() {
		if (substr($this->eventObj->eventAttachment->fileType, 0, 5) !== 'text/') return;
		
		$this->eventObj->eventData['hasThumbnail'] = true;
	}
	
	/**
	 * Actually generate the thumbnail.
	 */
	public function generateThumbnail() {
		// someone else already grabbed this one
		if (count($this->eventObj->eventData)) return;
		
		if (substr($this->eventObj->eventAttachment->fileType, 0, 5) !== 'text/') return;
		
		// load data
		$tinyAdapter = \wcf\system\image\ImageHandler::getInstance()->getAdapter();
		$adapter = \wcf\system\image\ImageHandler::getInstance()->getAdapter();
		$file = file($this->eventObj->eventAttachment->getLocation());
		
		// initialize our drawing sheeps
		$tinyAdapter->createEmptyImage(144, 144);
		$adapter->createEmptyImage(ATTACHMENT_THUMBNAIL_WIDTH, ATTACHMENT_THUMBNAIL_HEIGHT);
		$tinyAdapter->setColor(0x00, 0x00, 0x00);
		$adapter->setColor(0x00, 0x00, 0x00);
		
		$i = 1;
		foreach ($file as $line) {
			// tabs cannot be displayed with gdlib
			$line = str_replace("\t", "    ", \wcf\util\StringUtil::substring(\wcf\util\StringUtil::trim('.'.$line), 1));
			
			if (IMAGE_ADAPTER_TYPE == 'imagick') {
				$tinyAdapter->drawText($line, 5, $i * 13);
				$adapter->drawText($line, 5, $i * 13);
			}
			else {
				$tinyAdapter->drawText($line, 5, $i * 13 - 13);
				$adapter->drawText($line, 5, $i * 13 - 13);
			}
			
			$i++;
		}
		
		// and create the images
		$tinyThumbnailLocation = $this->eventObj->eventAttachment->getTinyThumbnailLocation();
		$thumbnailLocation = $this->eventObj->eventAttachment->getThumbnailLocation();
		
		$tinyAdapter->writeImage($tinyThumbnailLocation.'.png');
		rename($tinyThumbnailLocation.'.png', $tinyThumbnailLocation);
		$adapter->writeImage($thumbnailLocation.'.png');
		rename($thumbnailLocation.'.png', $thumbnailLocation);
		
		// calculate the thumbnail data
		$updateData = array();
		if (file_exists($tinyThumbnailLocation) && ($imageData = @getImageSize($tinyThumbnailLocation)) !== false) {
			$updateData['tinyThumbnailType'] = $imageData['mime'];
			$updateData['tinyThumbnailSize'] = @filesize($tinyThumbnailLocation);
			$updateData['tinyThumbnailWidth'] = $imageData[0];
			$updateData['tinyThumbnailHeight'] = $imageData[1];
		}
		
		if (file_exists($thumbnailLocation) && ($imageData = @getImageSize($thumbnailLocation)) !== false) {
			$updateData['thumbnailType'] = $imageData['mime'];
			$updateData['thumbnailSize'] = @filesize($thumbnailLocation);
			$updateData['thumbnailWidth'] = $imageData[0];
			$updateData['thumbnailHeight'] = $imageData[1];
		}
		
		$this->eventObj->eventData = $updateData;
	}
}
