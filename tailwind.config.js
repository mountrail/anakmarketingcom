import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// tailwind.config.js
export default {
    darkMode: 'class', // <--- Force dark mode only with a .dark class (which you won't use)
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
                branding: {
                    primary: '#FA9332',
                    dark: '#4D4D4D',
                    light: '#FFFFFF',
                    black: '#150D05',
                },
                secondary: {
                    pale: '#FFCF8D',
                    orange: '#FE7D00',
                    deep: '#FA6800',
                },
                essentials: {
                    inactive: '#B4B4B4',
                    alert: '#FF4747',
                    success: '#34C759',
                    default: '#007AFF',
                },
            },
        },
    },
    plugins: [forms],
};
