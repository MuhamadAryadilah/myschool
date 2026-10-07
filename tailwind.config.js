import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // netral slate kebiruan yang lembut
                gray: {
                    50: '#f6f8fa',
                    100: '#eef2f5',
                    200: '#e1e7ec',
                    300: '#cbd4dc',
                    400: '#9aa7b3',
                    500: '#6b7885',
                    600: '#525e6a',
                    700: '#3e4852',
                    800: '#2a323a',
                    900: '#1c2228',
                    950: '#11151a',
                },
                // warna utama: teal lembut
                brand: {
                    50: '#f0fafa',
                    100: '#d5f1f1',
                    200: '#aee3e4',
                    300: '#7accd0',
                    400: '#45b0b8',
                    500: '#2a939e',
                    600: '#207683',
                    700: '#1d5e69',
                    800: '#1c4d57',
                    900: '#1b4149',
                    950: '#0d2830',
                },
                // aksen: coral lembut
                accent: {
                    100: '#fde8e0',
                    400: '#f08a6c',
                    500: '#e0623f',
                    600: '#c24d2d',
                },
            },
        },
    },

    plugins: [forms],
};