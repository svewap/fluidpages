<?php
namespace FluidTYPO3\Fluidpages\ContentObject;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Contains FLUIDPAGETEMPLATE class object
 */
class FluidPageContentObject extends AbstractContentObject
{

    /**
     * @var ContentDataProcessor
     */
    protected $contentDataProcessor;

    /**
     * @param ContentObjectRenderer $cObj
     */
    public function __construct(ContentObjectRenderer $cObj)
    {
        parent::__construct($cObj);
        $this->contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
    }

    /**
     * @param ContentDataProcessor $contentDataProcessor
     */
    public function setContentDataProcessor($contentDataProcessor)
    {
        $this->contentDataProcessor = $contentDataProcessor;
    }

    /**
     * Rendering the cObject, FLUIDPAGETEMPLATE
     *
     * Configuration properties:
     * - variables array
     * - dataProcessing array of data processors which are classes to manipulate $data
     *
     * Example:
     * 10 = FLUIDPAGETEMPLATE
     * 10.variables {
     *   mylabel = TEXT
     *   mylabel.value = Label from TypoScript coming
     * }
     * 10.dataProcessing {
     *   1 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
     * }
     *
     * @param array $conf Array of TypoScript properties
     * @return string The HTML output
     */
    public function render($conf = [])
    {
        if (!is_array($conf)) {
            $conf = [];
        }

        $variables = $this->getContentObjectVariables($conf);
        $variables = $this->contentDataProcessor->process($this->cObj, $conf, $variables);


        $bootstrap = new Bootstrap();
        $content = $bootstrap->run(null,[
            'extensionName' => 'Fluidpages',
            'vendorName' => 'FluidTYPO3',
            'pluginName' => 'Page',
            'variables' => $variables,
        ]);

        $content = $this->applyStandardWrapToRenderedContent($content, $conf);

        return $content;
    }


    /**
     * Compile rendered content objects in variables array ready to assign to the view
     *
     * @param array $conf Configuration array
     * @return array the variables to be assigned
     * @throws \InvalidArgumentException
     */
    protected function getContentObjectVariables(array $conf)
    {
        $variables = [];
        $reservedVariables = ['data', 'current'];
        // Accumulate the variables to be process and loop them through cObjGetSingle
        $variablesToProcess = (array)$conf['variables.'];
        foreach ($variablesToProcess as $variableName => $cObjType) {
            if (is_array($cObjType)) {
                continue;
            }
            if (!in_array($variableName, $reservedVariables)) {
                $variables[$variableName] = $this->cObj->cObjGetSingle($cObjType, $variablesToProcess[$variableName . '.']);
            } else {
                throw new \InvalidArgumentException(
                    'Cannot use reserved name "' . $variableName . '" as variable name in FLUIDTEMPLATE.',
                    1288095720
                );
            }
        }
        $variables['data'] = $this->cObj->data;
        $variables['current'] = $this->cObj->data[$this->cObj->currentValKey];
        return $variables;
    }


    /**
     * Apply standard wrap to content
     *
     * @param string $content Rendered HTML content
     * @param array $conf Configuration array
     * @return string Standard wrapped content
     */
    protected function applyStandardWrapToRenderedContent($content, array $conf)
    {
        if (isset($conf['stdWrap.'])) {
            $content = $this->cObj->stdWrap($content, $conf['stdWrap.']);
        }
        return $content;
    }


}
