<?php
namespace B13\Pagetsconfig\Provider;

/*
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Page TsConfig relevant for this record
 */
class PageTsConfigForeignTableProvider implements FormDataProviderInterface
{
    /**
     * Merge type specific page TS to pageTsConfig
     *
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
        $mergedTsConfig = $result['pageTsConfig'];

        if (empty($result['pageTsConfig']['TCEFORM.']) || !is_array($result['pageTsConfig']['TCEFORM.'])) {
            $result['pageTsConfig'] = $mergedTsConfig;
            return $result;
        }
        $mergedTsConfig = $result['pageTsConfig'];
        $type = $result['recordTypeValue'];
        $table = $result['tableName'];
        if ($result['isInlineChild']) {
            $table = $result['inlineParentTableName'];
        }
        // Merge TCEFORM.[table name].[field].types.[type] over TCEFORM.[table name].[field]
        if (!empty($result['pageTsConfig']['TCEFORM.'][$table . '.'])
            && is_array($result['pageTsConfig']['TCEFORM.'][$table . '.'])
        ) {
            foreach ($result['pageTsConfig']['TCEFORM.'][$table . '.'] as $fieldNameWithDot => $fullFieldConfiguration) {
                $newFieldConfiguration = $fullFieldConfiguration;
                if (!empty($fullFieldConfiguration['types.']) && is_array($fullFieldConfiguration['types.'])) {
                    $typeSpecificConfiguration = $newFieldConfiguration['types.'];
                    if (!empty($typeSpecificConfiguration[$type . '.']['foreign_table_configuration.']) && is_array($typeSpecificConfiguration[$type . '.']['foreign_table_configuration.'])) {
                        foreach ($typeSpecificConfiguration[$type . '.']['foreign_table_configuration.'] as $overrideTable => $fieldConfigs) {
                            foreach ($fieldConfigs as $fieldName => $fieldConfig) {
                                $foreignOverriddenConfig = $mergedTsConfig['TCEFORM.'][$overrideTable][$fieldName];
                                if (!is_array($foreignOverriddenConfig)) {
                                    $foreignOverriddenConfig = array();
                                }
                                ArrayUtility::mergeRecursiveWithOverrule($foreignOverriddenConfig, $fieldConfig);
                                $mergedTsConfig['TCEFORM.'][$overrideTable][$fieldName] = $foreignOverriddenConfig;
                            }
                        }
                    }
                }
            }
        }

        $result['pageTsConfig'] = $mergedTsConfig;
        return $result;
    }
}
