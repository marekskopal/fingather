import {PortfolioData} from "@app/models/portfolio-data";
import {BenchmarkData} from "@app/models/benchmark-data";

export class PortfolioDataWithBenchmarkData extends PortfolioData {
    public benchmarkData: BenchmarkData|null;
}
