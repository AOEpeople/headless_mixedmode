<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// we are the ContentRenderingTemplate
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'headlessmixedmode/Configuration/TypoScript/';
