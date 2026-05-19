const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                hd: {
                    50:  '#eef0ff',
                    100: '#dde1ff',
                    200: '#c4c9f5',
                    500: '#6366F1',
                    600: '#5A4FCF',
                    700: '#4a46b8',
                    800: '#3F3D91',
                    900: '#312e72',
                },
                brand: {
                    DEFAULT: '#6366F1',
                    50:  '#eef0ff',
                    100: '#dde1ff',
                    500: '#6366F1',
                    600: '#5A4FCF',
                    700: '#4a46b8',
                    800: '#3F3D91',
                },
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
