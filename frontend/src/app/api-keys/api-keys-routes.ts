import {Route} from "@angular/router";
import {AddEditApiKeyComponent} from "@app/api-keys/add-edit-api-key/add-edit-api-key.component";
import {ApiKeysComponent} from "@app/api-keys/api-keys/api-keys.component";
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: ApiKeysComponent,
            },
            {
                path: 'add-api-key',
                component: AddEditApiKeyComponent,
            },
            {
                path: 'edit-api-key/:id',
                component: AddEditApiKeyComponent,
            },
        ],
    },
] satisfies Route[];
