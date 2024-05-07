import { BenchmarkData } from '@app/models/benchmark-data';
import { PortfolioData } from '@app/models/portfolio-data';

export interface PortfolioDataWithBenchmarkData extends PortfolioData {
    benchmarkData: BenchmarkData | null;
}
