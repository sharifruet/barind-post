/**
 * Thin client for the site's /api/v1 automation API (app/Controllers/Api/NewsController.php).
 *
 * The MCP server never touches the database: validation, dedup on source_url /
 * content hash, the auto-publish switch and every publish gate stay on the server,
 * exactly as they are for the n8n pipelines.
 */
export class BarindApi {
  /**
   * @param {{ baseUrl: string, apiKey: string, timeoutMs?: number, fetchImpl?: typeof fetch }} options
   */
  constructor({ baseUrl, apiKey, timeoutMs = 30_000, fetchImpl = fetch }) {
    this.baseUrl = baseUrl.replace(/\/+$/, '');
    this.apiKey = apiKey;
    this.timeoutMs = timeoutMs;
    this.fetchImpl = fetchImpl;
    // Public links are built from the API URL: https://site/api/v1 -> https://site
    this.siteUrl = this.baseUrl.replace(/\/api\/v1$/, '');
  }

  /**
   * @returns {Promise<{ status: number, data: any }>} status 0 = the request never got an HTTP answer
   */
  async request(method, path, { query, body } = {}) {
    const url = new URL(this.baseUrl + path);
    for (const [key, value] of Object.entries(query ?? {})) {
      if (value !== undefined && value !== null) url.searchParams.set(key, String(value));
    }

    let res;
    try {
      res = await this.fetchImpl(url, {
        method,
        headers: {
          Authorization: `Bearer ${this.apiKey}`,
          Accept: 'application/json',
          ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
        },
        body: body !== undefined ? JSON.stringify(body) : undefined,
        // A followed 301/302 turns a POST into a GET, which would silently drop the article
        // (http -> https, missing www, ...). Surface the redirect instead.
        redirect: 'manual',
        signal: AbortSignal.timeout(this.timeoutMs),
      });
    } catch (err) {
      const reason = err?.name === 'TimeoutError' ? `timed out after ${this.timeoutMs} ms` : (err?.cause?.message ?? err?.message ?? String(err));
      return { status: 0, data: { error: `Could not reach ${url.origin}: ${reason}` } };
    }

    if (res.status >= 300 && res.status < 400) {
      return {
        status: res.status,
        data: { error: `The API redirected to ${res.headers.get('location') ?? '(no location)'}; set BARIND_API_URL to the final URL.` },
      };
    }

    const text = await res.text();
    try {
      return { status: res.status, data: text === '' ? null : JSON.parse(text) };
    } catch {
      // cPanel error pages, maintenance pages, a wrong path that hits the public 404...
      return { status: res.status, data: { error: `Non-JSON response (HTTP ${res.status})`, body: text.slice(0, 500) } };
    }
  }

  categories() {
    return this.request('GET', '/categories');
  }

  tags() {
    return this.request('GET', '/tags');
  }

  exists(sourceUrl) {
    return this.request('GET', '/news/exists', { query: { source_url: sourceUrl } });
  }

  show(id) {
    return this.request('GET', `/news/${encodeURIComponent(id)}`);
  }

  recent(limit) {
    return this.request('GET', '/news/recent', { query: { limit } });
  }

  createNews(payload) {
    return this.request('POST', '/news', { body: payload });
  }

  articleUrl(slug) {
    return `${this.siteUrl}/news/${slug}`;
  }

  adminEditUrl(id) {
    return `${this.siteUrl}/admin/news/edit/${id}`;
  }
}
