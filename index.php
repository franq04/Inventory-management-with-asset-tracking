<?php

declare(strict_types=1);

if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
	$requestPath = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$scriptName = (string) $_SERVER['SCRIPT_NAME'];
	$scriptDir = str_replace('\\', '/', dirname($scriptName));

	if (is_string($requestPath) && $scriptDir !== '' && $scriptDir !== '.' && $scriptDir !== '/') {
		if (preg_match('#^' . preg_quote($scriptDir, '#') . '#i', $requestPath, $matches) === 1) {
			$normalizedDir = rtrim($matches[0], '/');
			$normalizedScriptName = $normalizedDir . '/index.php';

			$_SERVER['SCRIPT_NAME'] = $normalizedScriptName;
			$_SERVER['PHP_SELF'] = $normalizedScriptName;
		}
	}
}

require __DIR__ . '/public/index.php';
