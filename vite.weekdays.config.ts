import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig(({ mode }) => ({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/weekdays.ts'),
      name: 'SyrrepordersWeekdays',
      formats: ['iife'],
      fileName: () => 'syrreporders-weekdays.js',
    },
    outDir: 'js',
    emptyOutDir: false,
    sourcemap: mode === 'development',
    minify: mode !== 'development',
  },
}))
