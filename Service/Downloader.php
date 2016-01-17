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
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_USERAGENT      => self::USER_AGENT,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_CAINFO         => __DIR__ . '/cacert.pem',
		));

		$result = $this->curlExecFollow($ch);

		curl_close($ch);

		return $result;
	}

	protected function curlExecFollow(&$ch, $redirects = 5, $curloptHeader = false) {
		if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
			curl_setopt_array($ch, array(
				CURLOPT_HEADER         => $curloptHeader,
				CURLOPT_FOLLOWLOCATION => $redirects > 0,
				CURLOPT_MAXREDIRS      => $redirects,
			));

			return curl_exec($ch);
		}
		else {
			curl_setopt_array($ch, array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_HEADER         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FORBID_REUSE   => false,
			));

			do {
				$data = curl_exec($ch);

				if (curl_errno($ch)) {
					break;
				}

				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				if ($code != 301 && $code != 302 && $code != 303) {
					break;
				}

				$headerStart = strpos($data, "\r\n") + 2;
				$headerLength = strpos($data, "\r\n\r\n", $headerStart) + 2 - $headerStart;
				$headers = substr($data, $headerStart, $headerLength);
				if (!preg_match("~\r\n(?:Location|URI): *(.*?) *\r\n~", $headers, $matches)) {
					break;
				}

				$url = $matches[1];
				if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
					$location = $url;
					$origUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
					$url = rtrim($origUrl, '/') . '/' . ltrim($location, '/');
				}

				curl_setopt($ch, CURLOPT_URL, $url);
			} while (--$redirects);

			if (!$redirects) {
				return false;
			}

			if (!$curloptHeader) {
				$data = substr($data, strpos($data, "\r\n\r\n") + 4);
			}

			return $data;
		}
	}

}
