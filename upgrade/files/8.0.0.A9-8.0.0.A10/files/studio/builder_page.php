<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    TridentEndAdmin Trident Studio End Admin Pages
 * @ingroup     TridentStudio
 * @{
 */

require_once('./../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DOL_DIR_STUDIO_INC . 'utils.inc.php');

bx_import('BxDolLanguages');

bx_require_authentication(true);

$sType = bx_get('type');
if($sType === false)
    $sType = bx_get('bp_type');
$sType = $sType !== false ? bx_process_input($sType) : '';

$sPage = bx_get('page');
if($sPage === false)
    $sPage = bx_get('bp_page');
$sPage = $sPage !== false ? bx_process_input($sPage) : '';

bx_import('BxTemplStudioBuilderPage');
$oPage = new BxTemplStudioBuilderPage($sType, $sPage);
$oPage->init();

bx_import('BxDolStudioTemplate');
$oTemplate = BxDolStudioTemplate::getInstance();
$oTemplate->setPageNameIndex($oPage->getPageIndex());
$oTemplate->setPageHeader($oPage->getPageHeader());
$oTemplate->setPageContent('page_caption_code', $oPage->getPageCaption());
$oTemplate->setPageContent('page_attributes', $oPage->getPageAttributes());
$oTemplate->setPageContent('page_menu_code', $oPage->getPageMenu());
$oTemplate->setPageContent('page_main_code', $oPage->getPageCode());
$oTemplate->addCss($oPage->getPageCss());
$oTemplate->addJs($oPage->getPageJs());
$oTemplate->getPageCode();
/** @} */