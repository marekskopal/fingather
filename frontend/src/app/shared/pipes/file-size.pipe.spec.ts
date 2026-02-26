import { FileSizePipe } from './file-size.pipe';

describe('FileSizePipe', () => {
    let pipe: FileSizePipe;

    beforeEach(() => {
        pipe = new FileSizePipe();
    });

    it('returns "0 Bytes" for null', () => {
        expect(pipe.transform(null)).toBe('0 Bytes');
    });

    it('returns "0 Bytes" for 0', () => {
        expect(pipe.transform(0)).toBe('0 Bytes');
    });

    it('returns "1 B" for 1 byte', () => {
        expect(pipe.transform(1)).toBe('1 B');
    });

    it('returns "1 KB" for 1024 bytes', () => {
        expect(pipe.transform(1024)).toBe('1 KB');
    });

    it('returns "1 MB" for 1024 * 1024 bytes', () => {
        expect(pipe.transform(1024 * 1024)).toBe('1 MB');
    });

    it('returns "1.5 KB" for 1536 bytes', () => {
        expect(pipe.transform(1536)).toBe('1.5 KB');
    });
});
