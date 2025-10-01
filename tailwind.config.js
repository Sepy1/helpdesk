const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
         "./resources/**/*.blade.php",
  "./resources/**/*.js",
  "./resources/**/*.vue",
    ],

    theme: {
    extend: {
      colors: {
        // ganti sesuai palet yang kamu mau
        tombol: {
          DEFAULT: "#ff6600", // Blue-600
          50:  "#2cb403ff",
          100: "#10B981",
          500: "#ff6600",
          600: "#ff6600",
          700: "#ff6600",
        },

        tulisan: {
          DEFAULT: "#ffffffff", // Blue-600
          50:  "#ffffffff",
          100: "#ffffffff",
          500: "rgba(255, 255, 255, 0.94)",
          600: "#fdfdfdff",
          700: "#0e0000ff",
        },

        brand: {
          DEFAULT: "#064980", // Blue-600
          50:  "#7018ecff",
          100: "#064980",
          500: "#064980",
          600: "#064980",
          700: "#064980",
        },

        

      },
    },
  },

    plugins: [require('@tailwindcss/forms')],
};
