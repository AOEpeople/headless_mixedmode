<?php
namespace Aoe\HeadlessMixedmode\Frontend\DataProcessing;

use Helhum\TyposcriptRendering\Configuration\RecordRenderingConfigurationBuilder;
use Helhum\TyposcriptRendering\Renderer\RenderingContext;
use Helhum\TyposcriptRendering\Uri\TyposcriptRenderingUri;
use Helhum\TyposcriptRendering\Uri\ViewHelperContext;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ListTypeProcessor implements DataProcessorInterface
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
        $name = $processorConfiguration['listConfig'];
        if (isset($name[0]) && $name[0] === '<') {
            $key = trim(substr($name, 1));
            $cF = GeneralUtility::makeInstance(TypoScriptParser::class);
            // $name and $conf is loaded with the referenced values.
            list($name, $conf) = $cF->getVal($key, $this->getTypoScriptFrontendController()->tmpl->setup);

            // fake format for extbase
            $restoreExtbaseFormat = null;
            if (isset($this->getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_' . $cObj->data['list_type'] . '.']['format'])) {
                $restoreExtbaseFormat = $this->getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_' . $cObj->data['list_type'] . '.']['format'];
            }

            $outputFormat = 'json';
            if (isset($conf[$cObj->data['list_type'] . '.']['headlessMixedFormat'])) {
                $outputFormat = $conf[$cObj->data['list_type'] . '.']['headlessMixedFormat'];
            }
            $this->getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_' . $cObj->data['list_type'] . '.']['format'] = $outputFormat;

            $classicContent = $cObj->cObjGetSingle($conf[$cObj->data['list_type']], $conf[$cObj->data['list_type'] . '.']);

            // restore format
            if ($restoreExtbaseFormat !== null) {
                $this->getTypoScriptFrontendController()->tmpl->setup['plugin.']['tx_' . $cObj->data['list_type'] . '.']['format'] = $restoreExtbaseFormat;
            }

            if (substr(trim($classicContent), 0, 1) === '{') {
                // we have (supposedly) valid json
                $classicContent = json_decode($classicContent, JSON_OBJECT_AS_ARRAY);
            } else if (substr(trim($classicContent), 0, 15) === '<!--INT_SCRIPT.') {
                // intScripts are referenced via helhum/typoscriptrendering
                $classicContent = ['intScript' => $this->typoscriptRenderingUri($cObj, $cObj->data['list_type'])];
            } else {
                // anything else is just text
                $classicContent = ['text' => $classicContent];
            }

            return [
                $processorConfiguration['as'] => $classicContent
            ];
        }

        return [];
    }

    /**
     * @param ContentObjectRenderer $cObj
     * @param string $listType
     * @return mixed
     */
    protected function typoscriptRenderingUri($cObj, $listType)
    {
        $renderingPath = 'tt_content.list.20.' . $listType;
        $typolinkConfiguration = [
            'parameter' => $cObj->data['pid'] . ',' . $this->getTypoScriptFrontendController()->type,
            'additionalParams' => '&' . http_build_query([
                'tx_typoscriptrendering' => [
                    'context' => '{"record":"' . $cObj->getCurrentTable() . '_' . $cObj->data['uid'] . '","path":"' . $renderingPath . '"}'
                ],
                'tx_' . $listType => [
                    'format' => 'json'
                ]
            ]),
            'useCacheHash' => 1
        ];

        return $cObj->typoLink_URL($typolinkConfiguration);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

}