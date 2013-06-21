<?php

namespace Skin;

class DbSkinBuilder extends SkinBuilder
{
	private $cacheExpire = 600; // expire files every 600 seconds

	private $cacheExpireArchive = 86400; // expire files every 86400 seconds (24 hours)

	public function setSkinParams()
	{
		$skinConfigCacheKey = $_SERVER["SERVER_NAME"] . DIRECTORY_SEPARATOR . "WebSettings";

		$skinConfigCache = $this->cacher->get($skinConfigCacheKey);

		if ($skinConfigCache->isValid()) {

			$row = $skinConfigCache->getValue();

		} else {

			dbInitCommon();

			$params = array(
				"SkinID" => array(-1, "int", 0),
				"SkinURL" => array($_SERVER["SERVER_NAME"],"string",50)
			);

			dbExecSproc("wGetSkinConfig_v2", $params, true, $row);

			if (intval($row['SkinID'])) {
				$this->cacher->set($skinConfigCacheKey, $row, $this->cacheExpire);
			}
		}

		$this->skin->set('id', $row["SkinID"]);
		$this->skin->set('name', $row["SkinName"]);
		$this->skin->set('networkID', $row["NetworkID"]);
		$this->skin->set('network', $row["NetworkName"] ? $row["NetworkName"] : "888");

		$this->skin->set('url', "http://" . $row["SkinURL"]);
		$this->skin->set('MobileURL', $row["MobileURL"]);
		$this->skin->set('OID', $row["CashierOID"]);
		$this->skin->set('KID', $row["CashierKID"]);
		$this->skin->set('siteCode', $row["Prefix"]);
		$this->skin->set('title', $row["SiteTitle"]);
		$this->skin->set('AffiliateContactEmail', $row["AffiliateContactEmail"]);
		$this->skin->set('Language', $row["Language"]);
		$this->skin->set('languageIso3', $row["LanguageISO"]);
		$this->skin->set('DefaultCountry', $row["DefaultCountryCode"]);
		$this->skin->set('DefaultCountryIso3', $row["DefaultCountryCodeISO3"]);
		$this->skin->set('DefaultCurrency', $row["DefaultCurrencyCode"]);
		$this->skin->set('gameSounds', $row["GameSoundsLanguage"]);

		if ($row["SpecialColors"]) {
			$this->skin->set('gameColours', $this->skin->get('id'));
		}

		$this->skin->set('gameSite', $row["SpecialIGSettings"] ? $this->skin->get('id') : 'default');
		$this->skin->set('gameBanners', $row["SpecialBanners"] ? $this->skin->get('name') : 'default');
		$this->skin->set('supportEmail', $row["SupportEmail"]);
		$this->skin->set('InMigration', $row["MigrationStatus"]);
		$this->skin->set('NetworkPlatformVersion', $row["Version"]);
		$this->skin->set('IsFacebookSupported', $row["IsFaceBookSupported"]);
		$this->skin->set('RegistrationURL', $row["RegistrationURL"]);
		$this->skin->set('MobileLP', $row["MobileLP"]);
	}

	public function setSkinArchiveParams()
	{

		$skinArchiveConfigCacheKey = $_SERVER["SERVER_NAME"] . DIRECTORY_SEPARATOR . "WebArchiveSettings";

		$skinArchiveConfigCache = $this->cacher->get($skinArchiveConfigCacheKey);

		if ($skinArchiveConfigCache->isValid()) {

			$archiveConfigResult = $skinArchiveConfigCache->getValue();

		} else {

			dbInitRepCommon();

			$params = array("URL" => array($_SERVER["SERVER_NAME"], "string", 50));

			dbExecSproc("wArchiveConfig", $params, true, $archiveConfigResult);

			if ($archiveConfigResult["PlayerTransactions"]) {
				$this->cacher->set($skinArchiveConfigCacheKey, $archiveConfigResult, $this->cacheExpireArchive);
			}
		}

		$this->skin->set('dtPlayerTransactions', $archiveConfigResult["PlayerTransactions"]);
		$this->skin->set('dtPlayerLoyaltyTransactions', $archiveConfigResult["PlayerLoyaltyTransactions"]);
		$this->skin->set('dtMygames', $archiveConfigResult["Mygames"]);
	}

}