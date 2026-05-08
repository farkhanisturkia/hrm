import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'white-sm': '0 0 20px rgba(255, 255, 255, 0.15)',
                'white-md': '0 0 40px rgba(255, 255, 255, 0.25)',
                'white-lg': '0 0 60px rgba(255, 255, 255, 0.40)',
            },
            colors: {
                primary: {
                    50: '#f2f7fc',
                    100: '#e1eff8',
                    200: '#c5dff2',
                    300: '#99c6ea',
                    400: '#66a3de',
                    500: '#3f83d1',
                    600: '#2876bc', // <--- Warna Utama (RGB 40 118 188)
                    700: '#215f9a',
                    800: '#1e5180',
                    900: '#1e4469',
                    950: '#132b43',
                }
            }
        },
    },

    plugins: [forms],
};