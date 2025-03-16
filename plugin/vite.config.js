import { defineConfig } from 'vite';
import path from 'path';
import fs from 'fs/promises';
import CleanCSS from 'clean-css';

// Plugin to reorganise CSS, minify, and generate source maps
function buildCSS() {
  return {
    name: 'build-css',
    async writeBundle(options, bundle) {
      const outDir = options.dir;

      // Find CSS files that have '-css' in the name (our CSS entries)
      const cssFiles = Object.keys(bundle).filter(
        (name) => name.endsWith('.css') && name.includes('-css')
      );

      for (const fileName of cssFiles) {
        const sourcePath = path.join(outDir, fileName);
        const sourceCode = await fs.readFile(sourcePath, 'utf-8');

        // Remove '-css' suffix to get the desired base name
        const baseName = path.basename(fileName, '.css'); // e.g., 'admin-css'
        const newBase = baseName.replace(/-css$/, '');
        const newCssName = `${newBase}.css`;
        const newCssPath = path.join(outDir, 'css', newCssName);

        // Ensure css/ folder exists
        await fs.mkdir(path.join(outDir, 'css'), { recursive: true });

        // Write unminified CSS
        await fs.writeFile(newCssPath, sourceCode);

        // Minify with source map
        const minified = new CleanCSS({
          sourceMap: true,
          sourceMapInlineSources: true,
        }).minify({
          [sourcePath]: { styles: sourceCode },
        });

        if (minified.errors.length) {
          console.error('CSS minify errors:', minified.errors);
          continue;
        }

        // Write minified CSS
        const minCssName = `${newBase}.min.css`;
        const minCssPath = path.join(outDir, 'css', minCssName);
        await fs.writeFile(minCssPath, minified.styles);

        // Write source map
        if (minified.sourceMap) {
          const mapName = `${newBase}.min.css.map`;
          const mapPath = path.join(outDir, 'css', mapName);
          await fs.writeFile(mapPath, JSON.stringify(minified.sourceMap));
        }

        // Delete the original file from the root (or assets/ folder)
        await fs.unlink(sourcePath);
      }

      // Optional: clean up any empty assets/ folder that might have been created
      const assetsDir = path.join(outDir, 'assets');
      try {
        const files = await fs.readdir(assetsDir);
        if (files.length === 0) {
          await fs.rmdir(assetsDir);
          console.log('🗑️ Removed empty assets/ folder');
        }
      } catch (err) {
        // Folder doesn't exist – ignore
      }
    },
  };
}

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    minify: false,           // we handle CSS minification ourselves
    sourcemap: true,         // for JS source maps

    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'assets/src/js/admin.js'),
        captcha: path.resolve(__dirname, 'assets/src/js/captcha.js'),
        notice: path.resolve(__dirname, 'assets/src/js/notice.js'),

        'admin-css': path.resolve(__dirname, 'assets/src/css/admin.css'),
        'captcha-css': path.resolve(__dirname, 'assets/src/css/captcha.css'),
      },

      output: {
        entryFileNames: 'js/[name].js',   // unminified JS (Vite won't minify because minify:false)
        // Put all assets (including CSS) directly in outDir – our plugin will move CSS
        assetFileNames: '[name][extname]',
      },
    },
  },
  plugins: [buildCSS()],
});