import {Route} from '@angular/router';
import {AddEditApiKeyComponent} from '@app/api-keys/add-edit-api-key/add-edit-api-key.component';
import {ApiKeysComponent} from '@app/api-keys/api-keys/api-keys.component';
import {AddEditGroupComponent} from '@app/groups/components/add-edit/add-edit-group.component';
import {GroupListComponent} from '@app/groups/components/group-list/group-list.component';
import {BenchmarkAssetsComponent} from '@app/settings/components/benchmark-assets/benchmark-assets.component';
import {SettingsLayoutComponent} from '@app/settings/components/settings-layout/settings-layout.component';

export default [
    {
        path: '',
        component: SettingsLayoutComponent,
        children: [
            {
                path: '',
                children: [
                    {
                        path: '',
                        redirectTo: 'groups',
                        pathMatch: 'full',
                    },
                    {
                        path: 'groups',
                        children: [
                            {
                                path: '',
                                component: GroupListComponent,
                            },
                            {
                                path: 'add-group',
                                component: AddEditGroupComponent,
                            },
                            {
                                path: 'edit-group/:id',
                                component: AddEditGroupComponent,
                            },
                        ],
                    },
                    {
                        path: 'api-keys',
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
                    {
                        path: 'benchmark-assets',
                        component: BenchmarkAssetsComponent,
                    },
                ],
            },
        ],
    },
] satisfies Route[];
