import {Route} from '@angular/router';
import { DividendsHistoryComponent } from '@app/dividends/components/history/dividends-history.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: DividendsHistoryComponent },
        ]
    }
] satisfies Route[];
