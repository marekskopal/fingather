import {
    ChangeDetectionStrategy, Component, inject,
} from '@angular/core';
import {TranslateService} from "@ngx-translate/core";

@Component({
    selector: 'fingather-language-selector',
    templateUrl: 'language-selector.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LanguageSelectorComponent {
    private readonly translateService = inject(TranslateService);

    protected languages: string[] = this.translateService.getLangs();
    protected currentLanguage: string = this.translateService.currentLang;

    protected changeLanguage(lang: string): void {
        localStorage.setItem('currentLanguage', lang);
        this.translateService.use(lang);
        this.currentLanguage = this.translateService.currentLang;
    }
}
