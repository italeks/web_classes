<?php

namespace Skin;

abstract class SkinBuilder
{
	protected $skin;
	protected $cacher;

	protected abstract function setSkinParams();

	protected abstract function setSkinArchiveParams();

	public function getSkin()
	{
		return $this->skin;
	}

	public function constructSkin(\Cache\CacheInterface $caher, array $config, array $networkConfig)
	{
		$this->skin = new Skin();
		$this->cacher = $caher;
		// Run order is importent
		$this->setHardcodedParams();
		$this->setSkinParams();
		$this->setSkinArchiveParams();
		$this->mergeWithNetworkParams($networkConfig);
		$this->setParamsFromConfig($config);
	}

	protected function setHardcodedParams()
	{

		$cleanedServerName = str_ireplace(array("AFFILIATE.", "AFFILIATE1.", "AFFILIATES."), "WWW.", strtoupper($_SERVER["SERVER_NAME"]));

		$affiliateSite = str_ireplace(array("www.", "www1."), "", $_SERVER["SERVER_NAME"]);

		if (!stristr($_SERVER["SERVER_NAME"], "affiliate")) {
			$affiliateSite = "affiliate." . $affiliateSite;
		}

		$this->skin->set('URL', "http://" . $cleanedServerName);
		$this->skin->set('siteDomain', '');
		$this->skin->set('error_message', '');
		$this->skin->set('message', '');
		$this->skin->set('status', '');
		$this->skin->set('AffiliateContactEmail', 'affiliates@bingomanager.net');
		$this->skin->set('affiliateExpiry', 360);
		$this->skin->set('numberOfLoginsPerMachine', 3);
		$this->skin->set('affiliateSite', $affiliateSite);
	}

	protected function mergeWithNetworkParams(array $network)
	{
		$networkName = strtolower($this->skin->get('network'));

		foreach ($network[$networkName] as $key => $value) {

			if (!$this->skin->has($key)) {
				$this->skin->set($key, $value);
			}
		}
	}

	protected function setParamsFromConfig(array $CONFIG)
	{
		$this->skin->set('gamePath', $CONFIG["gamePath"]);
		$this->skin->set('webRegister', $CONFIG["webRegister"]);
		$this->skin->set('paymentURL', $CONFIG["paymentURL"]."/start.php");
	}


}