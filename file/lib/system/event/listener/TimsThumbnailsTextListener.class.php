<?php
namespace wcf\system\event\listener;

/**
 * Adds a new route to RouteHandler
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
		if ($this->eventObj->eventAttachment->fileType == 'text/plain') {
			$this->eventObj->eventData['hasThumbnail'] = true;
		}
	}
}
