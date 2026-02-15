<?php

declare(strict_types=1);

namespace FinGather\Service\Export;

use Nette\Utils\FileSystem;
use RuntimeException;

final class PdfConverter
{
	public function convert(string $xlsxPath): string
	{
		$outDir = dirname($xlsxPath);

		$userInstallationDir = sys_get_temp_dir() . '/libreoffice_' . uniqid();
		mkdir($userInstallationDir);

		$command = sprintf(
			'HOME=%s libreoffice --headless --convert-to pdf --outdir %s %s 2>&1',
			escapeshellarg($userInstallationDir),
			escapeshellarg($outDir),
			escapeshellarg($xlsxPath),
		);

		exec($command, $output, $resultCode);

		FileSystem::delete($userInstallationDir);

		if ($resultCode !== 0) {
			throw new RuntimeException('LibreOffice conversion failed: ' . implode("\n", $output));
		}

		$pdfPath = preg_replace('/\.xlsx$/', '.pdf', $xlsxPath);

		if ($pdfPath === null || !file_exists($pdfPath)) {
			throw new RuntimeException('PDF file was not created');
		}

		return $pdfPath;
	}
}
