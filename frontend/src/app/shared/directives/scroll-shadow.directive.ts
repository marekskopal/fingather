import {
    AfterContentInit,
    Directive,
    ElementRef,
    inject,
    OnDestroy,
    OnInit, Renderer2
} from "@angular/core";
import {auditTime, fromEvent, Subject, takeUntil} from "rxjs";

@Directive({
    standalone: true,
    //eslint-disable-next-line @angular-eslint/directive-selector
    selector: '[scrollShadow]',
})
export class ScrollShadowDirective implements OnInit, AfterContentInit, OnDestroy {
    private readonly el = inject(ElementRef);
    private readonly renderer = inject(Renderer2);

    private canScrollRightClass: string = 'can-scroll-right';
    private canScrollLeftClass: string = 'can-scroll-left';
    private auditTimeMs: number = 125;

    private readonly destroy$: Subject<void> = new Subject();

    private wrapperElement: HTMLElement;

    public ngOnInit(): void {
        fromEvent(this.el.nativeElement, 'scroll').pipe(
            auditTime(this.auditTimeMs),
            takeUntil(this.destroy$),
        ).subscribe(() => {
            this.setClasses();
        })
    }

    public ngAfterContentInit(): void {
        this.createWrapper();

        this.setClasses();
    }

    public ngOnDestroy(): void {
        this.destroy$.next();
        this.destroy$.complete();
    }

    private createWrapper(): void {
        const nativeElement = this.el.nativeElement;
        const parent = this.el.nativeElement.parentNode;

        this.wrapperElement = this.renderer.createElement("div");

        this.renderer.addClass(this.wrapperElement, 'scroll-shadow-wrapper');
        this.renderer.insertBefore(parent, this.wrapperElement, nativeElement);
        this.renderer.removeChild(parent, nativeElement);
        this.renderer.appendChild(this.wrapperElement, nativeElement);
    }

    private setClasses(): void {
        const nativeElement = this.el.nativeElement;

        const canScrollLeft = nativeElement.scrollLeft > 0;
        const canScrollRight = nativeElement.scrollLeft + nativeElement.clientWidth < nativeElement.scrollWidth;

        this.renderer.removeClass(this.wrapperElement, this.canScrollRightClass);
        this.renderer.removeClass(this.wrapperElement, this.canScrollLeftClass);

        if (canScrollRight) {
            this.renderer.addClass(this.wrapperElement, this.canScrollRightClass);
        }

        if (canScrollLeft) {
            this.renderer.addClass(this.wrapperElement, this.canScrollLeftClass);
        }
    }
}
