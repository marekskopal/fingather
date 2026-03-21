<?php

declare(strict_types=1);

namespace FinGather\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;

final class FileResponse extends Response
{
	public function __construct(string $filePath, string $fileName, string $contentType)
	{
		$fileSize = filesize($filePath);

		$body = fopen($filePath, 'r');
		if ($body === false) {
			throw new \RuntimeException(sprintf('Cannot open file for reading: %s', $filePath));
		}

		$headers = [
			'Content-Type' => $contentType,
			'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
		];

		if ($fileSize !== false) {
			$headers['Content-Length'] = (string) $fileSize;
		}

		parent::__construct(new Stream($body), 200, $headers);
	}
}
