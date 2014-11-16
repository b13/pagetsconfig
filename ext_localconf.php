<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// XCLASS TCEFORMs / FormEngine
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\FormEngine'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\FormEngine',
);

// XCLASS IRRE class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\Element\\InlineElement'] = array(
    'className' => 'B13\\Pagetsconfig\\Xclass\\FormEngineInlineElement',
);