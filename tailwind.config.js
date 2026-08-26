import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
            fontFamily: {
                sans: ['Inter', 'Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // === Primary — Blue (brand utama) ===
                primary: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',   // ← brand color utama
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },

                // === Surface — Neutral abu untuk background & card ===
                surface: {
                    0:   '#ffffff',
                    50:  '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },

                // === Success — Green ===
                success: {
                    50:  '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                },

                // === Warning — Amber ===
                warning: {
                    50:  '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                },

                // === Danger — Red ===
                danger: {
                    50:  '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                },

                // === Info — Sky ===
                info: {
                    50:  '#f0f9ff',
                    100: '#e0f2fe',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                },
            },

            borderRadius: {
                'xs':  '0.375rem',  // 6px
                'sm':  '0.5rem',    // 8px
                'md':  '0.625rem',  // 10px
                'lg':  '0.75rem',   // 12px
                'xl':  '1rem',      // 16px — card standard
                '2xl': '1.25rem',   // 20px — card besar
                '3xl': '1.5rem',    // 24px
            },

            boxShadow: {
                'card':   '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-md':'0 4px 6px -1px rgb(0 0 0 / 0.06), 0 2px 4px -2px rgb(0 0 0 / 0.04)',
                'card-lg':'0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.05)',
                'glow':   '0 0 0 3px rgb(37 99 235 / 0.15)',
                'glow-sm':'0 0 0 2px rgb(37 99 235 / 0.12)',
                'inner-sm':'inset 0 1px 2px 0 rgb(0 0 0 / 0.04)',
            },

            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },

            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
                '72': '18rem',
                '80': '20rem',
                '88': '22rem',
                '96': '24rem',
            },

            animation: {
                'fade-in':    'fadeIn 0.2s ease-out',
                'slide-up':   'slideUp 0.2s ease-out',
                'slide-down': 'slideDown 0.2s ease-out',
                'scale-in':   'scaleIn 0.15s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },

            keyframes: {
                fadeIn: {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%':   { transform: 'translateY(8px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)',   opacity: '1' },
                },
                slideDown: {
                    '0%':   { transform: 'translateY(-8px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)',    opacity: '1' },
                },
                scaleIn: {
                    '0%':   { transform: 'scale(0.95)', opacity: '0' },
                    '100%': { transform: 'scale(1)',    opacity: '1' },
                },
            },

            transitionTimingFunction: {
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },

    plugins: [forms],
};
