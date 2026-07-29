# caveman

Skill mode bicara "caveman" — kompres respons AI jadi prosa ringkas tanpa kehilangan detail teknis. Dipakai untuk hemat token saat kerja intensif di repo ini.
 

## Yang dilakukan

Kompres tiap respons ke gaya prosa caveman: buang kata sandang, filler, basa-basi, dan hedging. Detail teknis, code block, error string, dan simbol tetap presisi/exact. Rata-rata memangkas 65% token output (terukur) tanpa mengorbankan akurasi.

Enam level intensitas:

| Level | Perubahan |
|-------|-----------|
| `lite` | Buang filler/hedging. Kalimat tetap utuh. Profesional tapi padat. |
| `full` | Default. Buang kata sandang, fragmen boleh, sinonim pendek. |
| `ultra` | Fragmen telanjang. Singkatan (DB, auth, fn). Panah untuk sebab-akibat. |
| `wenyan-lite` | Register Bahasa Tionghoa Klasik, kompresi ringan. |
| `wenyan-full` | Maksimum 文言文. Reduksi karakter 80–90%. |
| `wenyan-ultra` | Kompresi klasik ekstrem. |

**Aturan auto-clarity:** caveman turun ke prosa normal untuk peringatan keamanan, konfirmasi aksi ireversibel (mis. `git push --force`, hapus migrasi), urutan multi-langkah yang berisiko salah baca kalau difragmenkan, dan saat user mengulang pertanyaan yang sama. Kembali ke mode ringkas setelah bagian jelas itu selesai.

## Cara pakai

```
/caveman              # mode full (default)
/caveman lite          # kompresi lebih ringan
/caveman ultra         # kompresi ekstrem
/caveman wenyan         # Bahasa Tionghoa Klasik
stop caveman            # kembali ke prosa normal
```

## Contoh

Pertanyaan: "Why does my React component re-render?"

Prosa normal:
> Your component re-renders because you create a new object reference each render. Wrapping it in `useMemo` will fix the issue.

Caveman (full):
> New object ref each render. Inline object prop = new ref = re-render. Wrap in `useMemo`.

Caveman (ultra):
> Inline obj prop → new ref → re-render. `useMemo`.

## Referensi
 
- Salinan terpasang untuk Claude Code: `.claude/skills/caveman/` 