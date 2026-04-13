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
        vitalfit: {
          rosa: '#EC4899',      // placeholder
          morado: '#7C3AED',    // placeholder
          amarillo: '#F59E0B',  // placeholder
          lavanda: '#A78BFA',   // placeholder
          esmeralda: '#059669', // placeholder
          mandarina: '#F97316', // placeholder
          rojo: '#DC2626',      // placeholder
        }
      }
        },
    },

    plugins: [forms],
};
