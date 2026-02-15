<?php

declare(strict_types=1);

namespace FinGather\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;

final class FileResponse extends Response
{
	public function __construct(string $filePath, string $fileName, string $contentType)
	{
		$contents = (string) file_get_contents($filePath);
		$fileSize = strlen($contents);

		$body = fopen('php://temp', 'r+');
		assert($body !== false);
		fwrite($body, $contents);
		rewind($body);

		parent::__construct(new Stream($body), 200, [
			'Content-Type' => $contentType,
			'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
			'Content-Length' => (string) $fileSize,
		]);
	}
}
