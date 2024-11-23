import {Route} from '@angular/router';
import { DashboardComponent } from '@app/dashboard/components/dashboard/dashboard.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '', component: DashboardComponent,
            },
        ],
    },
] satisfies Route[];
