import {Route} from '@angular/router';
import {AccountInfoComponent} from "@app/account/components/account-info/account-info.component";
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: AccountInfoComponent },
        ],
    },
] satisfies Route[];
