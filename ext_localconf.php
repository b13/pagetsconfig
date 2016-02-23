<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
/** 6.2-related */
// XCLASS TCEFORMs / FormEngine
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\FormEngine'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\FormEngine',
);

// XCLASS IRRE class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\Element\\InlineElement'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\FormEngineInlineElement',
);

/** for v7 */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\Utility\\FormEngineUtility'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\FormEngineUtility',
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\Container\\InlineRecordContainer'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\InlineRecordContainer',
);

if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.3')) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\B13\Pagetsconfig\Provider\PageTsConfigForeignTableProvider::class] = array(
		\TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class
	);
}