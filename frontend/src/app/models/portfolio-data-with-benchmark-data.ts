import { BenchmarkData } from '@app/models/benchmark-data';
import { PortfolioData } from '@app/models/portfolio-data';

export class PortfolioDataWithBenchmarkData extends PortfolioData {
    public benchmarkData: BenchmarkData | null;
}
