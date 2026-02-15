<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\FileResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\TaxReportCalculator;
use FinGather\Service\Export\PdfConverter;
use FinGather\Service\Export\TaxReportExcelExporter;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TaxReportExportController
{
	public function __construct(
		private readonly TaxReportCalculator $taxReportCalculator,
		private readonly TaxReportExcelExporter $taxReportExcelExporter,
		private readonly PdfConverter $pdfConverter,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::TaxReportExportXlsx->value)]
	public function actionExportXlsx(ServerRequestInterface $request, int $portfolioId, int $year): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$taxReport = $this->taxReportCalculator->calculate($user, $portfolio, $year);
		$xlsxPath = $this->taxReportExcelExporter->export($taxReport, $portfolio->currency->symbol);

		try {
			return new FileResponse(
				$xlsxPath,
				'tax-report-' . $year . '.xlsx',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			);
		} finally {
			@unlink($xlsxPath);
		}
	}

	#[RouteGet(Routes::TaxReportExportPdf->value)]
	public function actionExportPdf(ServerRequestInterface $request, int $portfolioId, int $year): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$taxReport = $this->taxReportCalculator->calculate($user, $portfolio, $year);
		$xlsxPath = $this->taxReportExcelExporter->export($taxReport, $portfolio->currency->symbol);
		$pdfPath = $this->pdfConverter->convert($xlsxPath);

		try {
			return new FileResponse($pdfPath, 'tax-report-' . $year . '.pdf', 'application/pdf');
		} finally {
			@unlink($xlsxPath);
			@unlink($pdfPath);
		}
	}
}
