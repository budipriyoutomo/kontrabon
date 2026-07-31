import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import animate from 'tailwindcss-animate';

/**
 * Warna dibaca dari CSS variable di resources/css/app.css.
 * Format <alpha-value> membuat utility bernuansa alpha tetap jalan,
 * mis. bg-primary/50 -> hsl(var(--primary) / 0.5).
 */
const token = (name) => `hsl(var(--${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class'],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        // Varian komponen (helper ala CVA) didefinisikan sebagai string class di PHP.
        './app/View/**/*.php',
        './app/Support/**/*.php',
    ],

    theme: {
        container: {
            center: true,
            padding: '2rem',
            screens: {
                '2xl': '1400px',
            },
        },

        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                border: token('border'),
                input: token('input'),
                ring: token('ring'),
                background: token('background'),
                foreground: token('foreground'),

                primary: {
                    DEFAULT: token('primary'),
                    foreground: token('primary-foreground'),
                },
                secondary: {
                    DEFAULT: token('secondary'),
                    foreground: token('secondary-foreground'),
                },
                destructive: {
                    DEFAULT: token('destructive'),
                    foreground: token('destructive-foreground'),
                },
                success: {
                    DEFAULT: token('success'),
                    foreground: token('success-foreground'),
                },
                warning: {
                    DEFAULT: token('warning'),
                    foreground: token('warning-foreground'),
                },
                info: {
                    DEFAULT: token('info'),
                    foreground: token('info-foreground'),
                },
                muted: {
                    DEFAULT: token('muted'),
                    foreground: token('muted-foreground'),
                },
                accent: {
                    DEFAULT: token('accent'),
                    foreground: token('accent-foreground'),
                },
                popover: {
                    DEFAULT: token('popover'),
                    foreground: token('popover-foreground'),
                },
                card: {
                    DEFAULT: token('card'),
                    foreground: token('card-foreground'),
                },
                sidebar: {
                    DEFAULT: token('sidebar'),
                    foreground: token('sidebar-foreground'),
                    accent: token('sidebar-accent'),
                    'accent-foreground': token('sidebar-accent-foreground'),
                    border: token('sidebar-border'),
                    ring: token('sidebar-ring'),
                },
            },

            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },

            keyframes: {
                'accordion-down': {
                    from: { height: '0' },
                    to: { height: 'var(--radix-accordion-content-height)' },
                },
                'accordion-up': {
                    from: { height: 'var(--radix-accordion-content-height)' },
                    to: { height: '0' },
                },
                'collapsible-down': {
                    from: { height: '0', opacity: '0' },
                    to: { height: 'var(--collapsible-height)', opacity: '1' },
                },
                'collapsible-up': {
                    from: { height: 'var(--collapsible-height)', opacity: '1' },
                    to: { height: '0', opacity: '0' },
                },
            },

            animation: {
                'accordion-down': 'accordion-down 0.2s ease-out',
                'accordion-up': 'accordion-up 0.2s ease-out',
                'collapsible-down': 'collapsible-down 0.2s ease-out',
                'collapsible-up': 'collapsible-up 0.2s ease-out',
            },
        },
    },

    plugins: [
        /*
         * Strategy 'class' dipakai supaya plugin forms tidak lagi menimpa
         * seluruh elemen form secara global. Kontrol form digayakan penuh oleh
         * komponen sendiri; hanya <x-checkbox> yang masih meminjam gaya plugin
         * lewat class form-checkbox untuk menggambar kotak centangnya.
         */
        forms({ strategy: 'class' }),
        animate,
    ],
};
