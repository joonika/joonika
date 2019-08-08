<?php namespace Joonika;
if(!defined('jk')) die('Access Not Allowed !');

class Theme
{
    public $siteTitle="";
    public $siteDescription="";
    public $siteKeywords=[];
    public $option=[];

    /**
     * Theme constructor.
     */
    public function __construct()
    {

            $this->siteTitle=jk_options_get('siteTitle_websiteID_'.JK_WEBSITE_ID.'-'.JK_LANG);
            $this->siteDescription=jk_options_get('siteDescription_websiteID_'.JK_WEBSITE_ID.'-'.JK_LANG);
            $this->siteKeywords=jk_options_get('siteKeywords_websiteID_'.JK_WEBSITE_ID.'-'.JK_LANG);
    }


}