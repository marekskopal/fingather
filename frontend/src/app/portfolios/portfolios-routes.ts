import {Route} from '@angular/router';
import {AddEditPortfolioComponent} from "@app/portfolios/components/add-edit/add-edit-portfolio.component";
import { ListComponent } from '@app/portfolios/components/list/list.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: ListComponent
            },
            {
                path: 'add-portfolio',
                component: AddEditPortfolioComponent
            },
            {
                path: 'edit-portfolio/:id',
                component: AddEditPortfolioComponent
            },
        ]
    }
] satisfies Route[];
