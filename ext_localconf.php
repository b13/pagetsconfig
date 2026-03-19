<?php

defined('TYPO3') or die();

(function () {
    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 14) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][B13\Pagetsconfig\Backend\Form\FormDataProvider\PageTsConfigFix::class] = [
            'depends' => [
                \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
            ],
        ];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][B13\Pagetsconfig\Backend\Form\FormDataProvider\PageTsConfigFix::class] = [
            'depends' => [
                \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class,
            ],
        ];
    }

})();
