import {computed, inject, Injectable, signal} from '@angular/core';
import {AuthenticationService} from "@app/services/authentication.service";

@Injectable({ providedIn: 'root' })
export class ContentLayoutService {
    private readonly authenticationService = inject(AuthenticationService);

    public readonly $contentCenter = computed<boolean>(() =>
        !this.authenticationService.$isLoggedIn()
        || this.$thisContentCenter(),
    );

    private readonly $thisContentCenter = signal<boolean>(false);

    public setContentCenter(value: boolean): void {
        this.$thisContentCenter.set(value);
    }
}
