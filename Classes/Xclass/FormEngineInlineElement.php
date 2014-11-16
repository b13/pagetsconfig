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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class FormEngineInlineElement
 *
 * - adds default values to new IRRE elements so they properly rely on
 *   see -- DEFAULT VALUES -- in the code
 *
 * @package B13\Pagetsconfig
 */
class FormEngineInlineElement extends \TYPO3\CMS\Backend\Form\Element\InlineElement {

	/**
	 * Render the form-fields of a related (foreign) record.
	 *
	 * @param string $parentUid The uid of the parent (embedding) record (uid or NEW...)
	 * @param array $rec The table record of the child/embedded table (normaly post-processed by \TYPO3\CMS\Backend\Form\DataPreprocessor)
	 * @param array $config Content of $PA['fieldConf']['config']
	 * @return string The HTML code for this "foreign record
	 * @todo Define visibility
	 */
	public function renderForeignRecord($parentUid, $rec, $config = array()) {
		$foreign_table = $config['foreign_table'];
		$foreign_field = $config['foreign_field'];
		$foreign_selector = $config['foreign_selector'];
		// Register default localization content:
		$parent = $this->getStructureLevel(-1);
		if (isset($parent['localizationMode']) && $parent['localizationMode'] != FALSE) {
			$this->fObj->registerDefaultLanguageData($foreign_table, $rec);
		}
		// Send a mapping information to the browser via JSON:
		// e.g. data[<curTable>][<curId>][<curField>] => data-<pid>-<parentTable>-<parentId>-<parentField>-<curTable>-<curId>-<curField>
		$this->inlineData['map'][$this->inlineNames['form']] = $this->inlineNames['object'];
		// Set this variable if we handle a brand new unsaved record:
		$isNewRecord = !MathUtility::canBeInterpretedAsInteger($rec['uid']);
		// Set this variable if the record is virtual and only show with header and not editable fields:
		$isVirtualRecord = isset($rec['__virtual']) && $rec['__virtual'];
		// If there is a selector field, normalize it:
		if ($foreign_selector) {
			$rec[$foreign_selector] = $this->normalizeUid($rec[$foreign_selector]);
		}
		if (!$this->checkAccess(($isNewRecord ? 'new' : 'edit'), $foreign_table, $rec['uid'])) {
			return FALSE;
		}

		// added by b13 so new content elements can resolve [ctrl][type] to a parent record
		// -- DEFAULT VALUES --
		if ($isNewRecord && MathUtility::canBeInterpretedAsInteger($parentUid)) {
			$rec[$foreign_field] = $parent['table'] . '_' . $parentUid;
			if ($config['foreign_table_field']) {
				$rec[$config['foreign_table_field']] = $parent['table'];
			}
		}
		// -- DEFAULT VALUES --

		// Get the current naming scheme for DOM name/id attributes:
		$nameObject = $this->inlineNames['object'];
		$appendFormFieldNames = '[' . $foreign_table . '][' . $rec['uid'] . ']';
		$objectId = $nameObject . self::Structure_Separator . $foreign_table . self::Structure_Separator . $rec['uid'];
		// Put the current level also to the dynNestedStack of TCEforms:
		$this->fObj->pushToDynNestedStack('inline', $objectId);
		$class = '';
		if (!$isVirtualRecord) {
			// Get configuration:
			$collapseAll = isset($config['appearance']['collapseAll']) && $config['appearance']['collapseAll'];
			$expandAll = isset($config['appearance']['collapseAll']) && !$config['appearance']['collapseAll'];
			$ajaxLoad = isset($config['appearance']['ajaxLoad']) && !$config['appearance']['ajaxLoad'] ? FALSE : TRUE;
			if ($isNewRecord) {
				// Show this record expanded or collapsed
				$isExpanded = $expandAll || (!$collapseAll ? 1 : 0);
			} else {
				$isExpanded = $config['renderFieldsOnly'] || !$collapseAll && $this->getExpandedCollapsedState($foreign_table, $rec['uid']) || $expandAll;
			}
			// Render full content ONLY IF this is a AJAX-request, a new record, the record is not collapsed or AJAX-loading is explicitly turned off
			if ($isNewRecord || $isExpanded || !$ajaxLoad) {
				$combination = $this->renderCombinationTable($rec, $appendFormFieldNames, $config);
				$overruleTypesArray = isset($config['foreign_types']) ? $config['foreign_types'] : array();
				$fields = $this->renderMainFields($foreign_table, $rec, $overruleTypesArray);
				$fields = $this->wrapFormsSection($fields);
				// Replace returnUrl in Wizard-Code, if this is an AJAX call
				$ajaxArguments = GeneralUtility::_GP('ajax');
				if (isset($ajaxArguments[2]) && trim($ajaxArguments[2]) != '') {
					$fields = str_replace('P[returnUrl]=%2F' . rawurlencode(TYPO3_mainDir) . 'ajax.php', 'P[returnUrl]=' . rawurlencode($ajaxArguments[2]), $fields);
				}
			} else {
				$combination = '';
				// This string is the marker for the JS-function to check if the full content has already been loaded
				$fields = '<!--notloaded-->';
			}
			if ($isNewRecord) {
				// Get the top parent table
				$top = $this->getStructureLevel(0);
				$ucFieldName = 'uc[inlineView][' . $top['table'] . '][' . $top['uid'] . ']' . $appendFormFieldNames;
				// Set additional fields for processing for saving
				$fields .= '<input type="hidden" name="' . $this->prependFormFieldNames . $appendFormFieldNames . '[pid]" value="' . $rec['pid'] . '"/>';
				$fields .= '<input type="hidden" name="' . $ucFieldName . '" value="' . $isExpanded . '" />';
			} else {
				// Set additional field for processing for saving
				$fields .= '<input type="hidden" name="' . $this->prependCmdFieldNames . $appendFormFieldNames . '[delete]" value="1" disabled="disabled" />';
				if (!$isExpanded
					&& !empty($GLOBALS['TCA'][$foreign_table]['ctrl']['enablecolumns']['disabled'])
					&& $ajaxLoad
				) {
					$checked = !empty($rec['hidden']) ? ' checked="checked"' : '';
					$fields .= '<input type="checkbox" name="' . $this->prependFormFieldNames . $appendFormFieldNames . '[hidden]_0" value="1"' . $checked . ' />';
					$fields .= '<input type="input" name="' . $this->prependFormFieldNames . $appendFormFieldNames . '[hidden]" value="' . $rec['hidden'] . '" />';
				}
			}
			// If this record should be shown collapsed
			if (!$isExpanded) {
				$class = 't3-form-field-container-inline-collapsed';
			}
		}
		if ($config['renderFieldsOnly']) {
			$out = $fields . $combination;
		} else {
			// Set the record container with data for output
			if ($isVirtualRecord) {
				$class .= ' t3-form-field-container-inline-placeHolder';
			}
			if (isset($rec['hidden']) && (int)$rec['hidden']) {
				$class .= ' t3-form-field-container-inline-hidden';
			}
			$out = '<div class="t3-form-field-record-inline" id="' . $objectId . '_fields" data-expandSingle="' . ($config['appearance']['expandSingle'] ? 1 : 0) . '" data-returnURL="' . htmlspecialchars(GeneralUtility::getIndpEnv('REQUEST_URI')) . '">' . $fields . $combination . '</div>';
			$header = IconUtility::getSpriteIcon('apps-irre-' . ($class != '' ? 'collapsed' : 'expanded'));
			$header .= $this->renderForeignRecordHeader($parentUid, $foreign_table, $rec, $config, $isVirtualRecord);
			$out = '<div class="t3-form-field-header-inline" id="' . $objectId . '_header">' . $header . '</div>' . $out;
			// Wrap the header, fields and combination part of a child record with a div container
			$classMSIE = $this->fObj->clientInfo['BROWSER'] == 'msie' && $this->fObj->clientInfo['VERSION'] < 8 ? 'MSIE' : '';
			$class .= ' inlineDiv' . $classMSIE . ($isNewRecord ? ' inlineIsNewRecord' : '');
			$out = '<div id="' . $objectId . '_div" class="t3-form-field-container-inline ' . trim($class) . '">' . $out . '</div>';
		}
		// Remove the current level also from the dynNestedStack of TCEforms:
		$this->fObj->popFromDynNestedStack();
		return $out;
	}
} 