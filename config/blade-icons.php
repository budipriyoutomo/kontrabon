<?php

/*
 * Konfigurasi ini digabung (mergeConfigFrom) dengan config bawaan
 * blade-ui-kit/blade-icons, jadi hanya kunci yang perlu diubah yang ditulis.
 */

return [

    'components' => [

        'disabled' => false,

        /*
         * Komponen <x-icon> bawaan blade-icons dimatikan supaya nama itu bisa
         * dipakai komponen sendiri di resources/views/components/icon.blade.php,
         * yang membungkus set Lucide dengan ukuran dan atribut aksesibilitas
         * yang seragam. Ikon tetap bisa dipanggil langsung lewat <x-lucide-*>.
         */
        'default' => '',

    ],

];
