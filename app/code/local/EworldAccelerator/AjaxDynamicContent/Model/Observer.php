<?php

class EworldAccelerator_AjaxDynamicContent_Model_Observer {

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function generateJSON($observer) {
		// Change JSON if confugration change on "ADMIN" section
		if ($observer->getEvent()->getData('section') == 'admin') {
			$dropBefore = (int)Mage::getStoreConfig('admin/adc_group/eacc_adc_drop_before');
			$selectorsList = Mage::getStoreConfig('admin/adc_group/eacc_adc_selectors');

			// Starting generate JSON file
			$selectorsArray = explode(PHP_EOL, $selectorsList);
			$ajaxDynamicContentSystem = new EworldAccelerator_AjaxDynamicContent_Model_System($dropBefore);

			if (count($selectorsArray) > 0) {
				foreach ($selectorsArray as $lineNumber => $lineValue) {
					if (strpos($lineValue, ';') !== false) {
						list($currentSelector, $currentURL) = explode(';', $lineValue);
						$currentSelector = trim($currentSelector);
						$currentURL = trim($currentURL);

						if (EworldAccelerator_AjaxDynamicContent_Model_System::isValidURL($currentURL)) {
							$ajaxDynamicContentSystem->addSelector(new EworldAccelerator_AjaxDynamicContent_Model_Selector($currentSelector, $currentURL));
						}
					}
				}
				// Generate JSON file
				if (!$ajaxDynamicContentSystem->generateJSON()) {
					die('Error in AjaxDynamicContent JSON generation');
				}
			}
			// End JSON file generation
		}
	}

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function header($observer) {
		if (isset($GLOBALS['eworldAcceleratorCache']) && is_object($GLOBALS['eworldAcceleratorCache']) && $GLOBALS['eworldAcceleratorCache']->getCaching()) {
			if (strpos(get_class($observer->getEvent()->getData('action')), 'Adminhtml') === false) {
				$enabled = (int)Mage::getStoreConfig('admin/adc_group/eacc_adc_active');
				$testIP = trim(Mage::getStoreConfig('admin/adc_group/eacc_adc_test_ip'));
				if ($enabled == 1 || $enabled == 3 && $_SERVER['REMOTE_ADDR'] == $testIP) {
					$layout = Mage::app()->getLayout();
					$layout->getBlock('head')->addJs('EworldAccelerator/AjaxDynamicContent/front.js');
				}
			}
		}
	}
}
