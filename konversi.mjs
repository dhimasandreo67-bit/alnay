// konversi.mjs  (jalankan: node konversi.mjs)
import sharp from 'sharp';
import { readdirSync } from 'fs';
const dirs = [['public/images/home', 1400], ['public/images/kategori', 700], ['public/images/produk', 900]];
for (const [dir, w] of dirs) {
  for (const f of readdirSync(dir).filter(f => /\.(jpe?g|png)$/i.test(f) && !/logo|tapis/i.test(f))) {
    await sharp(`${dir}/${f}`).resize({ width: w, withoutEnlargement: true })
      .webp({ quality: 78 }).toFile(`${dir}/${f.replace(/\.\w+$/, '.webp')}`);
  }
}