# EduDesk on Cloudflare (edge) + Voroa (app)

Visitors use the Cloudflare address. Cloudflare serves/caches the CSS, JS and images and passes
everything else (login, forms, data) to the Laravel app on Voroa.

## One-time setup (on your computer)

```bash
cd cloudflare
npm install
npx wrangler login                      # opens the browser, sign in to Cloudflare
npx wrangler secret put EDGE_SECRET     # paste the same secret that is set on Voroa as EDGE_SECRET
npx wrangler deploy                     # prints https://edudesk.<your-name>.workers.dev
```

## Settings
- `ORIGIN` in `wrangler.toml` is the Voroa address of the app (`https://school.getvoroa.com`).
- Voroa env var `EDGE_SECRET` must equal the Worker secret `EDGE_SECRET`. Without the matching
  secret, the app ignores the edge's public-address header and links point to Voroa.

## Update
Re-run `npx wrangler deploy` after changing `src/index.js`. App changes only need a push to `master`.
