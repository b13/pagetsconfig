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

use TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface;
use TYPO3\CMS\Backend\Form\Exception\AccessDeniedContentEditException;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\InlineRelatedRecordResolver;
use TYPO3\CMS\Backend\Form\InlineStackProcessor;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\Utility\FormEngineUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Extend utility options to allow to override more values for IRRE
 */
class InlineRecordContainer extends \TYPO3\CMS\Backend\Form\Container\InlineRecordContainer {

	/**
	 * Creates main container for foreign record and renders it
	 *
	 * @param string $table The table name
	 * @param array $row The record to be rendered
	 * @param array $overruleTypesArray Overrule TCA [types] array, e.g to override [showitem] configuration of a particular type
	 * @return string The rendered form
	 */
	protected function renderRecord($table, array $row, array $overruleTypesArray = array()) {
		$domObjectId = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid']);

		$options = $this->data['tabAndInlineStack'];
		$options['tabAndInlineStack'][] = array(
			'inline',
			$domObjectId . '-' . $table . '-' . $row['uid'],
		);

		$command = 'edit';
		$vanillaUid = (int)$row['uid'];

		// If dealing with a new record, take pid as vanillaUid and set command to new
		if (!MathUtility::canBeInterpretedAsInteger($row['uid'])) {
			$command = 'new';
			$vanillaUid = (int)$row['pid'];
		}

		/** @var TcaDatabaseRecord $formDataGroup */
		$formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
		/** @var FormDataCompiler $formDataCompiler */
		$formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
		$formDataCompilerInput = [
			'command' => $command,
			'vanillaUid' => $vanillaUid,
			'tableName' => $table,
			'inlineData' => $this->inlineData,
			'tabAndInlineStack' => $options['tabAndInlineStack'],
			'overruleTypesArray' => $overruleTypesArray,
			'inlineStructure' => $this->data['inlineStructure'],
		];
		$options = $formDataCompiler->compile($formDataCompilerInput);
		$options['renderType'] = 'fullRecordContainer';

		// @todo: This hack merges data from already prepared row over fresh row again.
		// @todo: This really must fall ...
		foreach ($row as $field => $value) {
			if ($command === 'new' && is_string($value) && $value !== '' && array_key_exists($field, $options['databaseRow'])) {
				$options['databaseRow'][$field] = $value;
			}
		}

		// added by b13
		$options = $this->overrideTSconfigForOptions($table, $row, $options);
		return $this->nodeFactory->create($options)->render();
	}

	/**
	 * Merge TS config for each child record
	 *
	 * @param string $table
	 * @param array $row
	 * @param array $options
	 *
	 * @return array the modified options
	 */
	protected function overrideTSconfigForOptions($table, $row, $options) {
		$inlineParent = $this->inlineStackProcessor->getStructureLevel(-1);
		if ($inlineParent && $inlineParent['uid'] > 0) {
			$parentTable = $inlineParent['table'];
			$parentUid = $inlineParent['uid'];
			$parentField = $inlineParent['field'];
			if ($parentTable != $table && $parentUid != $row['uid']) {
				// get TSconfig of parent
				$parentRecord = BackendUtility::getRecord($parentTable, $parentUid);
				$formEngineUtility = GeneralUtility::makeInstance(FormEngineUtility::class);
				$parentTSconfig = $formEngineUtility->getTSconfigForTableRow($parentTable, $parentRecord, $parentField);

				// see if we find a inline setting, which needs to be overlaid
				if (isset($parentTSconfig['foreign_table_configuration.'][$table . '.'])) {
					$overriddenInlineTSconfig = $parentTSconfig['foreign_table_configuration.'][$table . '.'];
					if (is_array($options['pageTsConfigMerged']['TCEFORM.'][$table . '.'])) {
						$options['pageTsConfigMerged']['TCEFORM.'][$table . '.'] = array_merge($options['pageTsConfigMerged']['TCEFORM.'][$table . '.'], $overriddenInlineTSconfig);
					} else {
						$options['pageTsConfigMerged']['TCEFORM.'][$table . '.'] = $overriddenInlineTSconfig;
					}
				}
			}
		}
		return $options;
	}
}