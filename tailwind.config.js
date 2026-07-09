import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './config/**/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                surface: '#faf8ff',
                'surface-dim': '#d2d9f4',
                'surface-bright': '#faf8ff',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f2f3ff',
                'surface-container': '#eaedff',
                'surface-container-high': '#e2e7ff',
                'surface-container-highest': '#dae2fd',
                'on-surface': '#131b2e',
                'on-surface-variant': '#464555',
                'inverse-surface': '#283044',
                'inverse-on-surface': '#eef0ff',
                outline: '#777587',
                'outline-variant': '#c7c4d8',
                'surface-tint': '#4d44e3',
                primary: '#3525cd',
                'on-primary': '#ffffff',
                'primary-container': '#4f46e5',
                'on-primary-container': '#dad7ff',
                secondary: '#712ae2',
                'secondary-container': '#8a4cfc',
                tertiary: '#005338',
                'tertiary-container': '#006e4b',
                background: '#faf8ff',
                'on-background': '#131b2e',
                'surface-variant': '#dae2fd',
                'surface-subtle': '#f8fafc',
                'border-light': '#e2e8f0',
                'warning-amber': '#f59e0b',
                'ai-accent': '#8b5cf6',
                success: '#059669',
                danger: '#ba1a1a',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Geist', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-lg': ['3rem', { lineHeight: '3.5rem', fontWeight: '700', letterSpacing: '0' }],
                'headline-lg': ['2rem', { lineHeight: '2.5rem', fontWeight: '600', letterSpacing: '0' }],
                'headline-md': ['1.5rem', { lineHeight: '2rem', fontWeight: '600', letterSpacing: '0' }],
                'body-lg': ['1.125rem', { lineHeight: '1.75rem', fontWeight: '400' }],
                'body-md': ['1rem', { lineHeight: '1.5rem', fontWeight: '400' }],
                'body-sm': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '400' }],
                'label-md': ['0.875rem', { lineHeight: '1rem', fontWeight: '600', letterSpacing: '0' }],
                'label-sm': ['0.75rem', { lineHeight: '0.875rem', fontWeight: '600', letterSpacing: '0' }],
            },
            spacing: {
                gutter: '1.5rem',
                'margin-mobile': '1rem',
                'margin-desktop': '2.5rem',
            },
            maxWidth: {
                'container-max': '1200px',
            },
            borderRadius: {
                sm: '0.25rem',
                DEFAULT: '0.5rem',
                md: '0.75rem',
                lg: '1rem',
                xl: '1.5rem',
            },
            boxShadow: {
                soft: '0 2px 4px rgba(19, 27, 46, 0.04)',
                lift: '0 16px 40px rgba(19, 27, 46, 0.10)',
                panel: '0 24px 70px rgba(40, 48, 68, 0.16)',
                ai: '0 14px 35px rgba(139, 92, 246, 0.26)',
            },
        },
    },

    plugins: [forms],
};
