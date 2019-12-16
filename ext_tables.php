<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/**
 * Default TypoScript for headless mixed with fluid
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript/',
    'Headless Mixedmode (ContentRenderingTemplate)'
);
