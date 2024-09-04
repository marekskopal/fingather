import {Route} from '@angular/router';
import { HistoryComponent } from '@app/history/components/history/history.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: HistoryComponent
            },
        ]
    }
] satisfies Route[];
