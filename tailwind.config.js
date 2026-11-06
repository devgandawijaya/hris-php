import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#DC2626',
                    primary: '#DC2626',
                    secondary: '#991B1B',
                    surface: '#F8FAFC',
                    dark: '#1E293B',
                    muted: '#64748B',
                    success: '#16A34A',
                    warning: '#F59E0B',
                    info: '#2563EB',
                    danger: '#DC2626',
                },
                rihris: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                    800: '#991B1B',
                    900: '#7F1D1D',
                    950: '#450A0A',
                }
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                'landing': '1200px',
                'dashboard': '1440px',
            }
        },
    },

    plugins: [forms, typography],
};

