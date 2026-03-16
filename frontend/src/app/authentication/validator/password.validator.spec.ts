import {FormControl} from '@angular/forms';

import {getPasswordRequirements, passwordValidator} from './password.validator';

describe('getPasswordRequirements', () => {
    it('returns all false for empty string', () => {
        const req = getPasswordRequirements('');
        expect(req.minLength).toBe(false);
        expect(req.uppercase).toBe(false);
        expect(req.lowercase).toBe(false);
        expect(req.digit).toBe(false);
        expect(req.specialChar).toBe(false);
    });

    it('returns all true for a valid password', () => {
        const req = getPasswordRequirements('ValidPass1!');
        expect(req.minLength).toBe(true);
        expect(req.uppercase).toBe(true);
        expect(req.lowercase).toBe(true);
        expect(req.digit).toBe(true);
        expect(req.specialChar).toBe(true);
    });

    it('minLength is true at exactly 8 characters', () => {
        expect(getPasswordRequirements('Abcde1!x').minLength).toBe(true);
    });

    it('minLength is false at 7 characters', () => {
        expect(getPasswordRequirements('Abcd1!x').minLength).toBe(false);
    });

    it('detects missing uppercase', () => {
        expect(getPasswordRequirements('abcdef1!').uppercase).toBe(false);
    });

    it('detects present uppercase', () => {
        expect(getPasswordRequirements('Abcdef1!').uppercase).toBe(true);
    });

    it('detects missing lowercase', () => {
        expect(getPasswordRequirements('ABCDEF1!').lowercase).toBe(false);
    });

    it('detects present lowercase', () => {
        expect(getPasswordRequirements('ABCDEFa1!').lowercase).toBe(true);
    });

    it('detects missing digit', () => {
        expect(getPasswordRequirements('AbcdefGH!').digit).toBe(false);
    });

    it('detects present digit', () => {
        expect(getPasswordRequirements('AbcdefG1!').digit).toBe(true);
    });

    it('detects missing special character', () => {
        expect(getPasswordRequirements('AbcdefG12').specialChar).toBe(false);
    });

    it('detects present special character', () => {
        expect(getPasswordRequirements('AbcdefG1!').specialChar).toBe(true);
    });
});

describe('passwordValidator', () => {
    it('returns null for empty value', () => {
        expect(passwordValidator(new FormControl(''))).toBeNull();
    });

    it('returns null for null value', () => {
        expect(passwordValidator(new FormControl(null))).toBeNull();
    });

    it('returns null for a valid password', () => {
        expect(passwordValidator(new FormControl('ValidPass1!'))).toBeNull();
    });

    it('returns error for password missing uppercase', () => {
        expect(passwordValidator(new FormControl('validpass1!'))).toEqual({passwordRequirements: true});
    });

    it('returns error for password missing lowercase', () => {
        expect(passwordValidator(new FormControl('VALIDPASS1!'))).toEqual({passwordRequirements: true});
    });

    it('returns error for password missing digit', () => {
        expect(passwordValidator(new FormControl('ValidPassAB!'))).toEqual({passwordRequirements: true});
    });

    it('returns error for password missing special character', () => {
        expect(passwordValidator(new FormControl('ValidPass123'))).toEqual({passwordRequirements: true});
    });

    it('returns error for password shorter than 8 characters', () => {
        expect(passwordValidator(new FormControl('Val1!xy'))).toEqual({passwordRequirements: true});
    });
});
