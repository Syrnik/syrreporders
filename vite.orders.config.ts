import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig(({ mode }) => ({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/orders.ts'),
      name: 'SyrrepordersOrders',
      formats: ['iife'],
      fileName: () => 'syrreporders-orders.js',
    },
    outDir: 'js',
    emptyOutDir: false,
    sourcemap: mode === 'development',
    minify: mode !== 'development',
  },
}))
