import {Route} from '@angular/router';
import { ListComponent } from '@app/overviews/components/list/list.component';
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
        ],
    },
] satisfies Route[];
