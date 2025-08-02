module.exports = {
  mode: 'jit',
  purge: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      width: {
        "50vw": "50vw",
      },
      backgroundImage: theme => ({
        'checked-svg': `url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e")`
      })
    },
  },
  variants: {
    extend: {
      borderWidth: ['hover', 'focus'],
      backgroundColor: ['checked'],
      backgroundImage: ['checked'],
      borderColor: ['checked'],
    },
  },
  plugins: [],
}
