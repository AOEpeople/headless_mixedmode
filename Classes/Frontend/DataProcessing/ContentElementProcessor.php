<?php
namespace Aoe\HeadlessMixedmode\Frontend\DataProcessing;

use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ContentElementProcessor implements DataProcessorInterface
{

    /**
     * Process content object data
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $key = 'tt_content.' . $cObj->data['CType'];
        $cF = GeneralUtility::makeInstance(TypoScriptParser::class);
        // $name and $conf is loaded with the referenced values.
        list($name, $conf) = $cF->getVal($key, $this->getTypoScriptFrontendController()->tmpl->setup);
        if (isset($conf['20'])) {
            $classicContent = $cObj->cObjGetSingle($conf['20'], $conf['20.']);
            if (substr(trim($classicContent), 0, 1) === '{') {
                $classicContent = json_decode($classicContent, JSON_OBJECT_AS_ARRAY);
            } else {
                $classicContent = ['text' => $classicContent];
            }
            return [
                $processorConfiguration['as'] => $classicContent
            ];
        }

        return [];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

}