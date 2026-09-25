/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          dark: '#1E3A8A',   // Deep Blue សម្រាប់ Sidebar និង Headings
          accent: '#6366F1', // Indigo សម្រាប់ Active tab និងប៊ូតុងសំខាន់
          bg: '#F8FAFC',     // Slate ស្រាលសម្រាប់ Background ទូទៅ
        },
        badge: {
          green: '#10B981',  // Available / Active
          red: '#EF4444',    // Borrowed / Overdue
          blue: '#3B82F6',   // Reserved
          amber: '#F59E0B',  // Pending
        }
      }
    }
  },
  plugins: [],
}
