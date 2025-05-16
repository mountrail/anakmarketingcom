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
                xs: ['clamp(0.75rem, 0.5vw + 0.5rem, 0.875rem)', '1.4'],   // 12px to 14px
                sm: ['clamp(0.875rem, 0.75vw + 0.5rem, 1rem)', '1.5'],      // 14px to 16px
                base: ['clamp(1rem, 1vw + 0.5rem, 1.125rem)', '1.6'],       // 16px to 18px
                lg: ['clamp(1.125rem, 1vw + 0.75rem, 1.375rem)', '1.6'],    // 18px to 22px
                xl: ['clamp(1.25rem, 1vw + 1rem, 1.5rem)', '1.6'],          // 20px to 24px
                '2xl': ['clamp(1.5rem, 2vw + 1rem, 2rem)', '1.6'],          // 24px to 32px
                '3xl': ['clamp(1.875rem, 2vw + 1.25rem, 2.25rem)', '1.6'],  // 30px to 36px
                '4xl': ['clamp(2.25rem, 2.5vw + 1.25rem, 2.5rem)', '1.6'],  // 36px to 40px
                '5xl': ['clamp(3rem, 3vw + 1.5rem, 3.5rem)', '1.2'],        // 48px to 56px
                '6xl': ['clamp(3.75rem, 3vw + 2rem, 4rem)', '1.2'],         // 60px to 64px
                '7xl': ['clamp(4.5rem, 3vw + 2.5rem, 5rem)', '1.2'],        // 72px to 80px
                '8xl': ['clamp(6rem, 4vw + 2.5rem, 6.5rem)', '1.1'],        // 96px to 104px
                '9xl': ['clamp(8rem, 5vw + 2.5rem, 8.5rem)', '1.1'],        // 128px to 136px
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
