import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                brand: {
                    50:  '#F2F7F9',
                    100: '#E5EEF2',
                    200: '#D0DEE4',
                    300: '#BACFD7',
                    400: '#A5BFC9',
                    500: '#97B3BD',
                    600: '#8FA7B0',
                    700: '#5F737B',
                    800: '#47575D',
                    900: '#303A3F',
                    950: '#181D1F',
                },
            },
        },
    },

    plugins: [forms],
};
