/*
 * HEIC/HEIF helper (browser):
 * - heicExifTiff(buf)      : ambil blok EXIF (TIFF) dari file HEIC/HEIF (ISO-BMFF, item "Exif")
 * - injectExifIntoJpeg(...) : sisipkan EXIF tersebut ke JPEG hasil konversi, supaya GPS & waktu
 *                             foto ikut terbawa seperti foto JPEG biasa
 * - exifApp1Segment(tiff)   : bungkus TIFF menjadi segmen APP1 JPEG
 */
(function (root) {
    'use strict';

    function boxes(dv, u8, start, end) {
        const out = [];
        let o = start;
        while (o + 8 <= end) {
            let size = dv.getUint32(o);
            const type = String.fromCharCode(u8[o + 4], u8[o + 5], u8[o + 6], u8[o + 7]);
            let hdr = 8;
            if (size === 1) { size = Number(dv.getBigUint64(o + 8)); hdr = 16; }
            else if (size === 0) { size = end - o; }
            if (size < hdr || o + size > end) break;
            out.push({ type: type, start: o + hdr, end: o + size });
            o += size;
        }
        return out;
    }

    function heicExifTiff(buf) {
        const dv = new DataView(buf);
        const u8 = new Uint8Array(buf);
        const top = boxes(dv, u8, 0, buf.byteLength);
        const meta = top.find(b => b.type === 'meta');
        if (!meta) return null;

        const mBoxes = boxes(dv, u8, meta.start + 4, meta.end); // 4 byte: version/flags
        const iinf = mBoxes.find(b => b.type === 'iinf');
        const iloc = mBoxes.find(b => b.type === 'iloc');
        if (!iinf || !iloc) return null;

        // iinf: cari item_ID untuk tipe 'Exif'
        let p = iinf.start;
        const iver = u8[p];
        p += 4;
        if (iver === 0) { p += 2; } else { p += 4; }
        let exifId = null;
        for (const e of boxes(dv, u8, p, iinf.end)) {
            if (e.type !== 'infe') continue;
            let q = e.start;
            const v = u8[q];
            q += 4;
            let id;
            if (v >= 3) { id = dv.getUint32(q); q += 4; } else { id = dv.getUint16(q); q += 2; }
            q += 2; // item_protection_index
            if (v >= 2) {
                const t = String.fromCharCode(u8[q], u8[q + 1], u8[q + 2], u8[q + 3]);
                if (t === 'Exif') { exifId = id; break; }
            }
        }
        if (exifId === null) return null;

        // iloc: lokasi data item di dalam file
        p = iloc.start;
        const lver = u8[p];
        p += 4;
        const b1 = u8[p++];
        const b2 = u8[p++];
        const offSize = b1 >> 4, lenSize = b1 & 15;
        const baseSize = b2 >> 4, idxSize = lver >= 1 ? (b2 & 15) : 0;
        let icount;
        if (lver < 2) { icount = dv.getUint16(p); p += 2; } else { icount = dv.getUint32(p); p += 4; }

        const readN = (o, n) => {
            if (n === 0) return 0;
            if (n === 2) return dv.getUint16(o);
            if (n === 4) return dv.getUint32(o);
            if (n === 8) return Number(dv.getBigUint64(o));
            return u8[o];
        };

        for (let i = 0; i < icount; i++) {
            let id;
            if (lver < 2) { id = dv.getUint16(p); p += 2; } else { id = dv.getUint32(p); p += 4; }
            let method = 0;
            if (lver >= 1) { method = dv.getUint16(p) & 0x0f; p += 2; }
            p += 2; // data_reference_index
            const base = readN(p, baseSize); p += baseSize;
            const extCount = dv.getUint16(p); p += 2;
            let extOff = 0, extLen = 0;
            for (let k = 0; k < extCount; k++) {
                if (lver >= 1 && idxSize > 0) p += idxSize;
                const o = readN(p, offSize); p += offSize;
                const l = readN(p, lenSize); p += lenSize;
                if (k === 0) { extOff = o; extLen = l; }
            }
            if (id !== exifId) continue;
            if (method !== 0) return null; // hanya metode offset-file yang didukung
            const start = base + extOff;
            if (extLen < 8 || start + extLen > buf.byteLength) return null;
            // Item Exif diawali 4 byte offset ke header TIFF
            const tiffStart = start + 4 + dv.getUint32(start);
            const tiffEnd = start + extLen;
            if (tiffEnd <= tiffStart) return null;
            return buf.slice(tiffStart, tiffEnd);
        }
        return null;
    }

    // Salin TIFF dan set orientasi = 1. Pixel hasil konversi sudah tegak (orientasi diterapkan saat decode),
    // jadi tag orientasi asli tidak boleh dipakai lagi (akan membuat foto miring dua kali).
    function tiffSetOrientationOne(tiffBuf) {
        const u8 = new Uint8Array(tiffBuf.slice(0));
        const dv = new DataView(u8.buffer);
        const le = u8[0] === 0x49; // 'I' = little endian
        const r16 = o => dv.getUint16(o, le);
        const r32 = o => dv.getUint32(o, le);
        const ifd = r32(4);
        const n = r16(ifd);
        for (let i = 0; i < n; i++) {
            const e = ifd + 2 + i * 12;
            if (r16(e) === 0x0112) { dv.setUint16(e + 8, 1, le); break; }
        }
        return u8;
    }

    function exifApp1Segment(tiffBuf) {
        const fixed = tiffSetOrientationOne(tiffBuf);
        const payloadLen = 2 + 6 + fixed.byteLength; // panjang termasuk field panjang itu sendiri
        if (payloadLen > 65535) return null;         // terlalu besar untuk satu segmen APP1
        const seg = new Uint8Array(2 + payloadLen);
        seg[0] = 0xFF; seg[1] = 0xE1;
        seg[2] = payloadLen >> 8; seg[3] = payloadLen & 0xFF;
        seg.set([0x45, 0x78, 0x69, 0x66, 0x00, 0x00], 4); // "Exif\0\0"
        seg.set(fixed, 10);
        return seg;
    }

    function injectExifIntoJpeg(jpegBuf, tiffBuf) {
        const src = new Uint8Array(jpegBuf);
        if (src[0] !== 0xFF || src[1] !== 0xD8) return null;
        let seg = null;
        try { seg = exifApp1Segment(tiffBuf); } catch (e) { seg = null; } // TIFF rusak: lewati EXIF, jangan gagalkan foto
        if (!seg) return null;
        const out = new Uint8Array(src.length + seg.length);
        out.set(src.subarray(0, 2), 0);
        out.set(seg, 2);
        out.set(src.subarray(2), 2 + seg.length);
        return out.buffer;
    }

    function hasExifApp1(buf) {
        const u8 = new Uint8Array(buf);
        if (u8[0] !== 0xFF || u8[1] !== 0xD8) return false;
        let o = 2;
        while (o + 4 < u8.length && u8[o] === 0xFF) {
            const marker = u8[o + 1];
            if (marker === 0xDA) break; // mulai data gambar
            const len = (u8[o + 2] << 8) | u8[o + 3];
            if (marker === 0xE1 && u8[o + 4] === 0x45 && u8[o + 5] === 0x78) return true;
            o += 2 + len;
        }
        return false;
    }

    const api = {
        heicExifTiff: heicExifTiff,
        tiffSetOrientationOne: tiffSetOrientationOne,
        exifApp1Segment: exifApp1Segment,
        injectExifIntoJpeg: injectExifIntoJpeg,
        hasExifApp1: hasExifApp1
    };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    root.HeicExif = api;
})(typeof window !== 'undefined' ? window : globalThis);
