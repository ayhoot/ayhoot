// tailwind.config.js
export default {
  content: [
    './index.html',
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Montserrat', 'sans-serif'],
      },
    },
  },
  plugins: [
    require("daisyui")
    // Your other plugins, if any
  ],
  // DaisyUI specific configuration
  daisyui: {
    themes: ["light"], // Or your specific light themes
    darkTheme: false,
    styled: true,    // Important for component styles
    utils: true,     // Important for modifier classes like tooltip-top
    base: true,
    logs: true,
    // ...
  },
};
