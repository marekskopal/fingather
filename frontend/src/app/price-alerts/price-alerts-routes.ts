import {Route} from "@angular/router";
import {AddEditPriceAlertComponent} from "@app/price-alerts/add-edit-price-alert/add-edit-price-alert.component";
import {PriceAlertsComponent} from "@app/price-alerts/price-alerts/price-alerts.component";
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: PriceAlertsComponent,
            },
            {
                path: 'add-price-alert',
                component: AddEditPriceAlertComponent,
            },
            {
                path: 'edit-price-alert/:id',
                component: AddEditPriceAlertComponent,
            },
        ],
    },
] satisfies Route[];
