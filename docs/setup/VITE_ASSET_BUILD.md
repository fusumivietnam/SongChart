# Vite Asset Build on Laragon

Laravel's `@vite` directive requires either:

```text
public/hot
```

when the Vite development server is running, or:

```text
public/build/manifest.json
```

when production assets have been built.

## Recommended local build

From Laragon Terminal:

```bat
cd C:\laragon\www\songchart
scripts\build-assets-laragon.bat
```

The script:

1. validates Node.js and npm;
2. runs `npm ci` when a lockfile exists, otherwise `npm install`;
3. runs `npm run build`;
4. verifies `public/build/manifest.json`;
5. clears Laravel caches.

## Development server alternative

```bat
npm install
npm run dev
```

Keep that terminal open. Vite creates `public/hot`, and Laravel will load assets from the development server.

## Source archive policy

`node_modules/` and compiled `public/build/` are not required in source archives. They are machine-generated outputs.

The repository should commit `package-lock.json` after dependency resolution to make `npm ci` reproducible.

## Local fallback behavior

In local/testing environments, a missing manifest now displays a visible warning instead of returning HTTP 500. This fallback is intentionally unstyled and is not a substitute for building assets.

In production, a missing manifest remains a deployment error.
