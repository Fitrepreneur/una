<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Market Market
 * @ingroup     UnaModules
 *
 * @{
 */

/*
 * Module representation.
 */
class BxMarketTemplate extends BxBaseModTextTemplate
{
	protected $_aCurrency;

    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        $this->MODULE = 'bx_market';

        parent::__construct($oConfig, $oDb);

        $this->_aCurrency = $this->_oConfig->getCurrency();
    }

    public function entryRating($aData)
    {
        $CNF = &$this->getModule()->_oConfig->CNF;

    	$sVotes = '';
        $oVotes = BxDolVote::getObjectInstance($CNF['OBJECT_VOTES'], $aData['id']);
        if($oVotes) {
			$sVotes = $oVotes->getElementBlock(array('show_counter' => true, 'show_legend' => true));
			if(!empty($sVotes))
				$sVotes = $this->parseHtmlByName('entry-rating.html', array(
		    		'content' => $sVotes,
		    	));
        }

    	return $sVotes; 
    }

    public function entryText ($aData, $sTemplateName = 'entry-text.html')
    {
    	$sScreenshots = $this->getScreenshots($aData);

    	$this->addCss(BX_DIRECTORY_PATH_PLUGINS_PUBLIC . 'flickity/|flickity.css');
    	$this->addJs('flickity/flickity.pkgd.min.js');
    	return $this->parseHtmlByContent(parent::entryText($aData), array(
    		'bx_if:show_screenshots' => array(
    			'condition' => !empty($sScreenshots),
    			'content' => array(
    				'screenshots' => $sScreenshots
    			)
    		)
    	));
    }

    public function setCover($aData)
    {
		$CNF = &$this->getModule()->_oConfig->CNF;

		if(empty($aData[$CNF['FIELD_COVER']]))
			return;

		$mixedOptions = BxDolPage::getObjectInstanceByURI()->getPageCoverParams();
        if(empty($mixedOptions) || !is_array($mixedOptions))
        	return;

        //--- Get Cover image URL
        $oTranscoderCover = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_COVER']);
		if(empty($oTranscoderCover))
			return;

		$sCoverUrl = $oTranscoderCover->getFileUrl($aData[$CNF['FIELD_COVER']]);
		if(empty($sCoverUrl))
			return;

		//--- Get Thumbnail image URL
		$sThumbnailUrl = '';
		$bThumbnailUrl = false;
		if(!empty($aData[$CNF['FIELD_THUMB']])) {
		    $oTranscoderThumbnail = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_THUMB']);
    		if($oTranscoderThumbnail)
    			$sThumbnailUrl = $oTranscoderThumbnail->getFileUrl($aData[$CNF['FIELD_THUMB']]);

            $bThumbnailUrl = !empty($sThumbnailUrl);
		}

		$oCover = BxDolCover::getInstance($this);
		$oCover->setCoverImageUrl($sCoverUrl);
		$oCover->set(array_merge($mixedOptions, array(
			'bx_if:image' => array (
                'condition' => $bThumbnailUrl,
                'content' => array(
                	'icon_url' => $sThumbnailUrl
		        ),
            ),
            'bx_if:icon' => array (
                'condition' => !$bThumbnailUrl,
                'content' => array(
                    'icon' => $CNF['ICON']
                ),
            ),
            'bx_if:bg' => array (
                'condition' => true,
                'content' => array('image_url' => $sCoverUrl),
            ),
		)));
    }

    public function getScreenshots($aData)
    {
    	$oModule = $this->getModule();

    	$CNF = &$oModule->_oConfig->CNF;
    	$aPhotos = $oModule->serviceGetScreenshots($aData[$CNF['FIELD_ID']]);
		if(empty($aPhotos) || !is_array($aPhotos))
    		return '';

		return $this->parseHtmlByName('entry-screenshots.html', array(
    		'bx_repeat:items' => $aPhotos
    	));
    }
    
    public function getAuthorAddon ($aData, $oProfile)
    {
        $aAccount = $oProfile->getAccountObject()->getInfo();

        $sContent = $this->parseHtmlByName('entry-author.html', array(
    		'joined' => bx_time_js($aAccount['added']),
	    	'last_active' => bx_time_js($aAccount['logged']),
	    	'installs' => '?',
    	));

    	return $sContent . parent::getAuthorAddon ($aData, $oProfile);
    }

    public function getGhostTemplateFileOptions($sField, $aFile, $aVersions)
    {
    	$bField = !empty($aFile) && is_array($aFile) && isset($aFile[$sField]);
    	
    	$aTmplVarsVersions = array();
    	foreach ($aVersions as $aVersion) {
    		if($aVersion['type'] != 'version' || empty($aVersion['version']))
    			continue;

			$aTmplVarsVersions[] = array(
				'value' => $aVersion['version'],
				'title' =>  $aVersion['version'],
				'bx_if:selected' => array(
					'condition' => $bField && $aFile[$sField] == $aVersion['version'],
					'content' => array()
				)
			);
    	}

    	return $this->parseHtmlByName('form_ghost_template_file_options.html', array(
    		'bx_repeat:versions' => $aTmplVarsVersions
    	));    	
    }

    public function getGhostTemplateFile($oForm, $aContentInfo)
    {
    	$CNF = &$this->getModule()->_oConfig->CNF;

    	return $this->parseHtmlByName('form_ghost_template_file.html', array (
                'name' => $oForm->aInputs[$CNF['FIELD_FILE']]['name'],
                'content_id' => $oForm->aInputs[$CNF['FIELD_FILE']]['content_id'],
                'editor_id' => $CNF['FIELD_TEXT_ID'],
                'thumb_id' => isset($aContentInfo[$CNF['FIELD_PACKAGE']]) ? $aContentInfo[$CNF['FIELD_PACKAGE']] : 0,
                'bx_if:set_thumb' => array (
                    'condition' => true,
                    'content' => array(
            			'name_thumb' => $CNF['FIELD_PACKAGE'],
            		),
                ),
            ));
    }

    protected function getUnit ($aData, $aParams = array())
    {
        $oModule = $this->getModule();
    	$CNF = &$oModule->_oConfig->CNF;

    	$aUnit = parent::getUnit($aData, $aParams);
        $oPayment = BxDolPayments::getInstance();

        //--- Author Info
        list($sAuthorName, $sAuthorUrl, $sAuthorIcon) = $oModule->getUserInfo($aData[$CNF['FIELD_AUTHOR']]);
        $bAuthorIcon = !empty($sAuthorIcon);      

        //--- Main Info
        $sUrl = BX_DOL_URL_ROOT . BxDolPermalinks::getInstance()->permalink('page.php?i=' . $CNF['URI_VIEW_ENTRY'] . '&id=' . $aData[$CNF['FIELD_ID']]);
        $sLinkMore = ' <a title="' . bx_html_attribute(_t('_sys_read_more', $aData[$CNF['FIELD_TITLE']])) . '" href="' . $sUrl . '"><i class="sys-icon ellipsis-h"></i></a>';
        $sSummary = strmaxtextlen($aData[$CNF['FIELD_TEXT']], (int)getParam($CNF['PARAM_CHARS_SUMMARY']), $sLinkMore);
        $sSummaryPlain = BxTemplFunctions::getInstance()->getStringWithLimitedLength(strip_tags($sSummary), (int)getParam($CNF['PARAM_CHARS_SUMMARY_PLAIN']));

        //--- Icon Info
        $sIconUrl = '';
        if(!empty($CNF['FIELD_THUMB']) && $aData[$CNF['FIELD_THUMB']]) {
            $oImagesTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_ICON']);
            if($oImagesTranscoder)
                $sIconUrl = $oImagesTranscoder->getFileUrl($aData[$CNF['FIELD_THUMB']]);

            if(empty($sIconUrl)) {
                $oImagesTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_THUMB']);
                if($oImagesTranscoder)
                    $sIconUrl = $oImagesTranscoder->getFileUrl($aData[$CNF['FIELD_THUMB']]);
            }
        }

        //--- Cover Info
        $sCoverUrl = '';
        if(!empty($CNF['FIELD_COVER']) && $aData[$CNF['FIELD_COVER']]) {
            $oImagesTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_COVER']);
            if($oImagesTranscoder)
                $sCoverUrl = $oImagesTranscoder->getFileUrl($aData[$CNF['FIELD_COVER']]);

            if(empty($sCoverUrl)) {
                $oImagesTranscoder = BxDolTranscoderImage::getObjectInstance($CNF['OBJECT_IMAGES_TRANSCODER_GALLERY']);
                if($oImagesTranscoder)
                    $sCoverUrl = $oImagesTranscoder->getFileUrl($aData[$CNF['FIELD_COVER']]);
            }
        }

        //--- Price Single
    	$bTmplVarsSingle = (float)$aData[$CNF['FIELD_PRICE_SINGLE']] != 0;
    	$aTmplVarsSingle = array();
    	if($bTmplVarsSingle) {
    		$aJsSingle = $oPayment->getAddToCartJs($aData[$CNF['FIELD_AUTHOR']], $this->_oConfig->getName(), $aData[$CNF['FIELD_ID']], 1);
    		if(!empty($aJsSingle) && is_array($aJsSingle)) {
    			list($sJsCode, $sSingleOnclick) = $aJsSingle;
    			
	    		$aTmplVarsSingle = array(
	    			'price_single_onclick' => $sSingleOnclick,
					'price_single' => _t('_bx_market_txt_price_single', $this->_aCurrency['sign'], $aData[$CNF['FIELD_PRICE_SINGLE']])
				);
    		}
    		else 
    			$bTmplVarsSingle = false;
    	}

    	//--- Price Recurring
    	$bTmplVarsRecurring = (float)$aData[$CNF['FIELD_PRICE_RECURRING']] != 0;
    	$aTmplVarsRecurring = array();
    	if($bTmplVarsRecurring) {
        	$aJsRecurring = $oPayment->getSubscribeJs($aData[$CNF['FIELD_AUTHOR']], '', $this->_oConfig->getName(), $aData[$CNF['FIELD_ID']], 1);
        	if(!empty($aJsRecurring) && is_array($aJsRecurring)) {
				list($sJsCode, $sRecurringOnclick) = $aJsRecurring;

	        	$aTmplVarsRecurring = array(
	        		'price_recurring_onclick' => $sRecurringOnclick,
					'price_recurring' => _t('_bx_market_txt_price_recurring', $this->_aCurrency['sign'], $aData[$CNF['FIELD_PRICE_RECURRING']], _t('_bx_market_txt_per_' . $aData[$CNF['FIELD_DURATION_RECURRING']] . '_short'))
				);
        	}
        	else 
        		$bTmplVarsRecurring = false;
    	}

    	$sActions = '';
    	$oActions = BxDolMenu::getObjectInstance($CNF['OBJECT_MENU_ACTIONS_SNIPPET']);
    	if($oActions) {
    	    $oActions->setContentId($aData[$CNF['FIELD_ID']]);
    	    $sActions = $oActions->getCode();
    	}

    	$sVotes = '';
    	$oVotes = BxDolVote::getObjectInstance($CNF['OBJECT_VOTES'], $aData[$CNF['FIELD_ID']]);
        if($oVotes)
			$sVotes = $oVotes->getElementBlock(array('show_counter' => false));

    	$aUnit = array_merge($aUnit, array(
    	    'actions' => $sActions,
    	    'bx_if:show_author_icon' => array(
                'condition' => $bAuthorIcon,
                'content' => array(
                    'author_icon' => $sAuthorIcon
                )
            ),
            'bx_if:show_author_icon_empty' => array(
                'condition' => !$bAuthorIcon,
                'content' => array()
            ),
            'author_url' => $sAuthorUrl,
            'author_title' => bx_html_attribute($sAuthorName),
            'author_name' => $sAuthorName,
            'bx_if:show_icon' => array (
                'condition' => $sIconUrl,
                'content' => array(
                	'icon_url' => $sIconUrl,
                ),
            ),
            'bx_if:show_icon_empty' => array (
                'condition' => !$sIconUrl,
                'content' => array(
                    'icon' => $CNF['ICON']
                ),
            ),
            'bx_if:show_cover' => array (
                'condition' => $sCoverUrl,
                'content' => array (
                    'summary_attr' => bx_html_attribute($sSummaryPlain),
                    'content_url' => $sUrl,
                    'cover_url' => $sCoverUrl,
                    'strecher' => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ', 40),
                ),
            ),
            'bx_if:show_cover_empty' => array (
                'condition' => !$sCoverUrl,
                'content' => array (
                    'summary_plain' => $sSummaryPlain,
                    'strecher' => mb_strlen($sSummaryPlain) > 240 ? '' : str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ', round((240 - mb_strlen($sSummaryPlain)) / 6)),
                ),
            ),
            'content_url' => $sUrl,
    		'votes' => $sVotes,
            'date' => bx_time_js($aData[$CNF['FIELD_ADDED']]),
    		'bx_if:show_single' => array(
    			'condition' => $bTmplVarsSingle,
    			'content' => $aTmplVarsSingle
    		),
    		'bx_if:show_recurring' => array(
    			'condition' => $bTmplVarsRecurring,
    			'content' => $aTmplVarsRecurring
    		),
    		'bx_if:show_free' => array(
    			'condition' => !$bTmplVarsSingle && !$bTmplVarsRecurring,
    			'content' => array()
    		),
    	));

    	return $aUnit;
    }

    protected function getAttachments($sStorage, $aData)
    {
    	$aFiles = $this->_oDb->getFile(array('type' => 'content_id_key_file_id', 'content_id' => $aData['id']));

    	$aAttachments = parent::getAttachments($sStorage, $aData);
    	if(empty($aAttachments) || !is_array($aAttachments))
    		return $aAttachments;

    	foreach($aAttachments as $iIndex => $aAttachment) {
    		$iAttachmentId = (int)$aAttachment['id'];

    		$aAttachments[$iIndex]['bx_if:main'] = array(
    			'condition' => (int)$aData['package'] == $iAttachmentId,
    			'content' => array()
    		);

    		$bAttachment = !empty($aFiles[$iAttachmentId]);
    		$aAttachments[$iIndex]['bx_if:not_image']['content'] = array_merge($aAttachments[$iIndex]['bx_if:not_image']['content'], array(
    			'file_type' => $bAttachment ? _t('_bx_market_form_entry_input_files_type_' . $aFiles[$iAttachmentId]['type']) : '',
    			'bx_if:show_version' => array(
    				'condition' => $bAttachment && $aFiles[$iAttachmentId]['type'] == BX_MARKET_FILE_TYPE_VERSION,
    				'content' => array(
    					'file_version' => $bAttachment ? $aFiles[$iAttachmentId]['version'] : ''
    				)
    			),
    			'bx_if:show_update' => array(
    				'condition' => $bAttachment && $aFiles[$iAttachmentId]['type'] == BX_MARKET_FILE_TYPE_UPDATE,
    				'content' => array(
    					'file_version_from_to' => $bAttachment ? _t('_bx_market_form_entry_input_files_version_from_x_to_y', $aFiles[$iAttachmentId]['version'], $aFiles[$iAttachmentId]['version_to']) : ''
    				)
    			)
    		));
    	}

    	return $aAttachments;
    }
}

/** @} */
