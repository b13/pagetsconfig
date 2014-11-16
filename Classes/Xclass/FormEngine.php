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

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Form\Element\InlineElement;
use TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * You can do crazy stuff like
 * TCEFORM.tt_content.image.types.textpic.foreign_table_configuration.sys_file_reference {
 *	title.label = Mein Titel overrriden 200000
 *	link.disabled = 1
 *	description.label = Oh yeah!
 * }
 * Class FormEngine
 * @package B13\Pagetsconfig\Xclass
 */
class FormEngine extends \TYPO3\CMS\Backend\Form\FormEngine {


	/**
	 * Constructor function, setting internal variables, loading the styles used.
	 *
	 * @return 	void
	 * @todo Define visibility
	 */
	public function __construct() {
		parent::__construct();
		$this->allowOverrideMatrix = array(
			'input' => array('size', 'max', 'readOnly', 'form_type', 'mode', 'placeholder', 'eval', 'cols', 'rows'),
			'text' => array('cols', 'rows', 'wrap', 'readOnly', 'form_type', 'mode', 'placeholder', 'eval', 'size'),
			'check' => array('cols', 'showIfRTE', 'readOnly'),
			'select' => array('size', 'autoSizeMax', 'maxitems', 'minitems', 'readOnly', 'treeConfig'),
			'group' => array('size', 'autoSizeMax', 'max_size', 'show_thumbs', 'maxitems', 'minitems', 'disable_controls', 'readOnly'),
			'inline' => array('appearance', 'behaviour', 'foreign_label', 'foreign_selector', 'foreign_unique', 'maxitems', 'minitems', 'size', 'autoSizeMax', 'symmetric_label', 'readOnly', 'foreign_types', 'foreign_table_configuration')
		);
	}

	/**
	 * Returns TSconfig for table/row
	 * Multiple requests to this function will return cached content so there is no performance loss in calling this many times since the information is looked up only once.
	 *
	 * @param string $table The table name
	 * @param array $row The table row (Should at least contain the "uid" value, even if "NEW..." string. The "pid" field is important as well, and negative values will be intepreted as pointing to a record from the same table.)
	 * @param string $field Optionally you can specify the field name as well. In that case the TSconfig for the field is returned.
	 * @return mixed The TSconfig values (probably in an array)
	 * @see BackendUtility::getTCEFORM_TSconfig()
	 * @todo Define visibility
	 */
	public function setTSconfig($table, $row, $field = '') {
		$mainKey = $table . ':' . $row['uid'];
		if ($this->inline) {
			$inlineParent = $this->inline->getStructureLevel(-1);
			if ($inlineParent && $inlineParent['uid'] > 0) {
				$parentTable = $inlineParent['table'];
				$parentUid = $inlineParent['uid'];
				$parentField = $inlineParent['field'];
				if ($parentTable != $table && $parentUid != $row['uid']) {
					$parentRecord = BackendUtility::getRecord($parentTable, $parentUid);
					$parentTSconfig = $this->setTSconfig($parentTable, $parentRecord, $parentField);

					// see if we find a inline setting, which needs to be overlaid
					if (isset($parentTSconfig['foreign_table_configuration.'][$table . '.'])) {
						$overriddenInlineTSconfig = $parentTSconfig['foreign_table_configuration.'][$table . '.'];
						$mainKey = $parentTable . ':' . $parentUid . ':' . $parentField . '::' . $mainKey;
					}
				}
			}
		}
		if (!isset($this->cachedTSconfig[$mainKey])) {
			// from TCEFORM.mytable.myfield with types selection
			$pageTSconfig = BackendUtility::getTCEFORM_TSconfig($table, $row);

			// from the resolving element
			if (is_array($overriddenInlineTSconfig)) {

				// remove "." on the first level keys
				$finalOverridenInlineTSconfig = array();
				foreach ($overriddenInlineTSconfig as $k => $v) {
					$finalOverridenInlineTSconfig[rtrim($k, '.')] = $v;
				}
				\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($pageTSconfig, $finalOverridenInlineTSconfig);
			}
			$this->cachedTSconfig[$mainKey] = $pageTSconfig;
		}

		if ($field) {
			return $this->cachedTSconfig[$mainKey][$field];
		} else {
			return $this->cachedTSconfig[$mainKey];
		}
	}

}