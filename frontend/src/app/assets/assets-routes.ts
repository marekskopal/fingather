
import {Route} from '@angular/router';
import {AddAssetComponent} from "@app/assets/components/add-asset/add-asset.component";
import { DetailComponent } from '@app/assets/components/detail/detail.component';
import { ListComponent } from '@app/assets/components/list/list.component';
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
                path: 'add-asset',
                component: AddAssetComponent,
            },
            {
                path: ':id',
                component: DetailComponent,
            },
        ],
    },
] satisfies Route[];
