<?php

namespace B13\Pagetsconfig\Backend\Form\FormDataProvider;

/*
 * This file is part of TYPO3 CMS-extension pagetsconfig by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTsConfigFix implements FormDataProviderInterface
{
    /**
     * Add page TsConfig
     *
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
        if (isset($result['pageTsConfig']['TCEFORM.'])) {
            $tables = $result['pageTsConfig']['TCEFORM.'];
            foreach ($tables as $tableName => $table) {
                if (is_array($table) && $result['tableName'].'.' === $tableName) {
                    if ($table['palettes.']) {
                        foreach ($table['palettes.'] as $paletteName => $paletteSettings) {
                            if (isset($paletteSettings)) {
                                $paletteName = substr($paletteName, 0, -1);
                                if (isset($result['processedTca']['palettes'][$paletteName])) {
                                    $paletteSettings = GeneralUtility::removeDotsFromTS($paletteSettings);
                                    ArrayUtility::mergeRecursiveWithOverrule(
                                        $result['processedTca']['palettes'][$paletteName],
                                        $paletteSettings
                                    );
                                }
                            }
                        }
                    }
                    foreach ($table as $columnName => $column) {
                        if (isset($column['config.']['overrideChildTca.'])) {
                            $columnName = substr($columnName,0,-1);
                            if (isset($result['processedTca']['columns'][$columnName]['config'])) {
                                $config = GeneralUtility::removeDotsFromTS($column['config.']);
                                ArrayUtility::mergeRecursiveWithOverrule($result['processedTca']['columns'][$columnName]['config'],$config);
                            }
                        }
                        if (isset($column['types.'], $result['processedTca']['ctrl']['type'])) {
                            foreach ($column['types.'] as $type => $typeConfig) {
                                $type = substr($type, 0, -1);
                                if ((string)$result['databaseRow'][$result['processedTca']['ctrl']['type']] === $type &&
                                    (isset($typeConfig['config.']['overrideChildTca.']))
                                ) {
                                    $config = GeneralUtility::removeDotsFromTS($typeConfig['config.']);
                                    ArrayUtility::mergeRecursiveWithOverrule(
                                        $result['processedTca']['columns'][$columnName]['config'],
                                        $config
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
}
