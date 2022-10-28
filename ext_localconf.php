<?php

defined('TYPO3_MODE') or die();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][B13\Pagetsconfig\Backend\Form\FormDataProvider\PageTsConfigFix::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
        ],
    ];
})();
