import {Route} from '@angular/router';
import { ListComponent } from '@app/overviews/components/list/list.component';
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
        ],
    },
] satisfies Route[];

