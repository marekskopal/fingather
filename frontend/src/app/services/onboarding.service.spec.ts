import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';

import { OnboardingService } from './onboarding.service';

describe('OnboardingService', () => {
    let service: OnboardingService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            imports: [HttpClientTestingModule],
            providers: [OnboardingService]
        });

        service = TestBed.inject(OnboardingService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    it('should be created', () => {
        expect(service).toBeTruthy();
    });

    it('onboardingComplete should return OkResponse', async () => {
        const mockResponse: OkResponse = { code: 200, message: 'ok' };

        service.onboardingComplete().then((response) => {
            expect(response).toEqual(mockResponse);
        });

        const req = httpMock.expectOne(`${environment.apiUrl}/onboarding-complete`);
        expect(req.request.method).toBe('POST');
        req.flush(mockResponse);
    });

    it('onboardingComplete should handle error', async () => {
        service.onboardingComplete().catch((error) => {
            expect(error.status).toBe(500);
        });

        const req = httpMock.expectOne(`${environment.apiUrl}/onboarding-complete`);
        expect(req.request.method).toBe('POST');
        req.flush(null, { status: 500, statusText: 'Server Error' });
    });
});
