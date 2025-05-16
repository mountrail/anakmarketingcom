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
            fontSize: {
                base: ['clamp(1rem, 1vw + 0.5rem, 1.125rem)', '1.6'], // 16px to 18px
                lg: ['clamp(1.125rem, 1vw + 0.75rem, 1.375rem)', '1.6'], // 18px to 22px
                xl: ['clamp(1.25rem, 1vw + 1rem, 1.5rem)', '1.6'], // 20px to 24px
                '2xl': ['clamp(1.5rem, 2vw + 1rem, 2rem)', '1.6'], // 24px to 32px
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
