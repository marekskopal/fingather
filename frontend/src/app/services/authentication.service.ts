import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import {Authentication} from "@app/models/authentication";

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private authenticationSubject: BehaviorSubject<Authentication>;
    public authentication: Observable<Authentication>;

    constructor(
        private router: Router,
        private http: HttpClient
    ) {
        this.authenticationSubject = new BehaviorSubject<Authentication>(JSON.parse(localStorage.getItem('authentication')));
        this.authentication = this.authenticationSubject.asObservable();
    }

    public get authenticationValue(): Authentication {
        return this.authenticationSubject.value;
    }

    login(username, password) {
        return this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
          email: username,
          password,
        })
            .pipe(map(authentication => {
                // store user details and jwt token in local storage to keep user logged in between page refreshes
                localStorage.setItem('authentication', JSON.stringify(authentication));
                this.authenticationSubject.next(authentication);
                return authentication;
            }));
    }

    logout() {
        // remove authentication from local storage and set current authentication to null
        localStorage.removeItem('authentication');
        this.authenticationSubject.next(null);
        this.router.navigate(['/authentication/login']);
    }
}
