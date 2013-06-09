<?php

namespace Hnizdil\Service;

class Downloader
{

	const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

	public function fetch($url) {

		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 5,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_FAILONERROR    => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_USERAGENT      => self::USER_AGENT,
		));

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;


	}

}
