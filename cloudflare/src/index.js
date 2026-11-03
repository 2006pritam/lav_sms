/**
 * EduDesk edge: the public address people visit lives on Cloudflare.
 *  - CSS, JavaScript and images (/assets, /global_assets) are cached at Cloudflare's edge.
 *  - Everything else (login, forms, pages, data) is passed to the Laravel app on Voroa.
 */
const STATIC = /^\/(assets|global_assets)\//;

export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    const origin = new URL(env.ORIGIN);
    const target = new URL(url.pathname + url.search, origin);

    const headers = new Headers(request.headers);
    headers.set('X-Public-Host', url.host);               // lets Laravel build links for this address
    if (env.EDGE_SECRET) headers.set('X-Edge-Secret', env.EDGE_SECRET);

    const readOnly = request.method === 'GET' || request.method === 'HEAD';
    const isStatic = readOnly && STATIC.test(url.pathname);

    const init = {
      method: request.method,
      headers,
      body: readOnly ? undefined : request.body,
      redirect: 'manual',                                  // hand redirects to the browser unchanged
    };
    if (isStatic) init.cf = { cacheEverything: true, cacheTtl: 2592000 };   // 30 days at the edge

    let res;
    try {
      res = await fetch(target.toString(), init);
    } catch (e) {
      return new Response('EduDesk is temporarily unreachable. Please try again in a moment.', { status: 502 });
    }

    res = new Response(res.body, res);

    // Keep redirects on the Cloudflare address, not the Voroa one.
    const loc = res.headers.get('Location');
    if (loc && loc.startsWith(origin.origin)) {
      res.headers.set('Location', url.origin + loc.slice(origin.origin.length));
    }

    if (isStatic && res.ok) {
      res.headers.set('Cache-Control', 'public, max-age=86400');           // browsers re-check daily
    }
    return res;
  },
};
