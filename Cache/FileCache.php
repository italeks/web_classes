<?php

namespace Cache;

class FileCache implements CacheInterface
{
	private $key, $value, $ttl;
	private $extension = '.txt';
	private $defaultTtl = 2592000; // month
	private $cacheFilePath ;

	public function __construct(array $CONFIG)
	{
		$this->cacheFilePath = $CONFIG['environment'];
	}

	public function get($key)
	{
		$cacheFile = $this->getCacheFilePath($key);

		if (file_exists($cacheFile)) {

			$ttl = filemtime($cacheFile);

			if ($data = file_get_contents($cacheFile)) {

				$value = unserialize($data);

				return new CacheItem($key, $value, $ttl);
			}
		}

		return new CacheItem($key, null, 0);;

	}

	public function set($key, $value, $ttl = null)
	{

		if (!$ttl) {
			$ttl = $defaultTtl;
		}

		$cacheFile = $this->getCacheFilePath($key);

		$directory = dirname($cacheFile);

		if (!is_dir($directory)) {

			mkdir($directory, 0777, true);

		}

		if (file_put_contents($cacheFile, serialize($value))) {

			@chmod($cacheFile,0777);

			touch($cacheFile, time() + $ttl);

			return true;

		}

		return false;
	}

	private function getCacheFilePath($key)
	{
		$cacheFile = $this->cacheFilePath;
		$cacheFile .= str_replace('_', DIRECTORY_SEPARATOR, $key) . $this->extension;

		return $cacheFile;
	}

}