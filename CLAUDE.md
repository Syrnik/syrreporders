# syrreporders — Shop Plugin

Order statistics reports plugin for Webasyst Shop-Script.

## Build

JavaScript sources are in `src/` (TypeScript), built with Vite into `js/`.

```bash
npm install

npm run build          # Production build (minified, no sourcemaps)
npm run build:dev      # Development build (sourcemaps enabled)
npm run watch:orders   # Watch mode for orders report
npm run watch:weekdays # Watch mode for weekdays report
```

Output files (committed to repo):
- `js/syrreporders-orders.js` — built from `src/orders.ts`
- `js/syrreporders-weekdays.js` — built from `src/weekdays.ts`

Chart.js and chartjs-adapter-date-fns are bundled into each output file.
The `node_modules/` directory is git-ignored.

## Architecture

- UI 1.3 only (UI 2.0 not yet supported)
- jQuery and `$` are globals provided by Webasyst — not imported
- Each output file exposes one class on `window`: `SyrrepordersOrdersChart` / `SyrrepordersWeekdaysChart`
- Classes are instantiated via inline `<script>` in Smarty templates — no changes to templates needed after rebuild
