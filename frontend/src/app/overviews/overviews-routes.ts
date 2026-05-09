import {Route} from '@angular/router';
import {CostBasisComparisonComponent} from '@app/overviews/components/cost-basis-comparison/cost-basis-comparison.component';
import { ListComponent } from '@app/overviews/components/list/list.component';
import {TaxOptimizationComponent} from '@app/overviews/components/tax-optimization/tax-optimization.component';
import {TaxReportComponent} from '@app/overviews/components/tax-report/tax-report.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: ListComponent,
            },
            {
                path: 'tax-report/:year',
                component: TaxReportComponent,
            },
            {
                path: 'tax-report/:year/cost-basis-comparison',
                component: CostBasisComparisonComponent,
            },
            {
                path: 'tax-optimization',
                component: TaxOptimizationComponent,
            },
        ],
    },
] satisfies Route[];
