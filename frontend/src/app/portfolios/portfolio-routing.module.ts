import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import {AddEditPortfolioComponent} from "@app/portfolios/components/add-edit/add-edit-portfolio.component";
import { LayoutComponent } from '@app/portfolios/components/layout/layout.component';
import { ListComponent } from '@app/portfolios/components/list/list.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: 'add-portfolio', component: AddEditPortfolioComponent },
            { path: 'edit-portfolio/:id', component: AddEditPortfolioComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class PortfolioRoutingModule { }
