<?php
namespace B13\Pagetsconfig\Xclass;

/**
 * This file is part of "pagetsconfig", an extension for TYPO3 CMS.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Extend utility options to allow to override more values for IRRE
 */
class FormEngineUtility extends \TYPO3\CMS\Backend\Form\Utility\FormEngineUtility {

	static protected $allowOverrideMatrix = array(
		'input' => array('size', 'max', 'readOnly', 'form_type', 'mode', 'placeholder', 'eval', 'cols', 'rows'),
		'text' => array('cols', 'rows', 'wrap', 'readOnly', 'form_type', 'mode', 'placeholder', 'eval', 'size'),
		'check' => array('cols', 'showIfRTE', 'readOnly'),
		'select' => array('size', 'autoSizeMax', 'maxitems', 'minitems', 'readOnly', 'treeConfig'),
		'group' => array('size', 'autoSizeMax', 'max_size', 'show_thumbs', 'maxitems', 'minitems', 'disable_controls', 'readOnly'),
		'inline' => array('appearance', 'behaviour', 'foreign_label', 'foreign_selector', 'foreign_unique', 'maxitems', 'minitems', 'size', 'autoSizeMax', 'symmetric_label', 'readOnly', 'foreign_types', 'foreign_table_configuration')
	);
}