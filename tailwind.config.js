/** @type {import('tailwindcss').Config} */

const plugin = require('tailwindcss/plugin')

module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    plugin(function({ addComponents }) {
      addComponents({
        '.btn': {
          background: '#39b54a',
          color: '#fff',
        },
        '.each-cont': {
          border: '2px solid #39b54a',
        },
        '.alert': {
          padding: '20px',
          color: '#fff',
        },
        '.closebtn': {
          marginLeft: '15px',
          color: '#fff',
          fontWeight: 'bold',
          float: 'right',
          fontSize: '22px',
          lineHeight: '20px',
          cursor: 'pointer',
          transition: '0.3s',
          '&:hover' :{
            color: '#000',
          }
        },
        '.nav-link': {
          color: '#fff',
          fontWeight: 'bold',
          border: '1px solid #fff',
          padding: '8px 14px',
          borderRadius: '20px',
          '&:hover': {
            cursor: 'pointer',
            background: '#fff',
            color: 'green',
          }
        },
        '.logo': {
          width: '50px',
        }
      })
    }
    )   
  ],
}

