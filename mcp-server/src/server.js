import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import * as z from 'zod';

const INSTRUCTIONS = `Pushes news articles to the Barind Post website (a Bengali news portal).

Workflow:
1. Call list_categories once and pick a category_id (and list_tags if you want tags).
2. If the story comes from a source page, call check_source_url first: an article with the same
   source_url is rejected as a duplicate.
3. Call create_news. Articles are filed as drafts for the newsroom unless you pass publish=true AND
   the site allows it (auto-publish on, and the article passes every publish gate). The response
   always says what happened; a draft with publish_gates listed is not an error.

Writing rules: Bangla copy for language "bn" (the default), no untranslated English clauses; the
headline must be about what the body says; subtitle is one sentence; key points are 2-4 short lines.
Never invent facts that are not in the source.`;

/** Format a tool result: a one-line summary for the model, the full JSON for anything that parses it. */
function reply(summary, data, isError = false) {
  return {
    content: [{ type: 'text', text: `${summary}\n\n${JSON.stringify(data, null, 2)}` }],
    ...(data && typeof data === 'object' && !Array.isArray(data) ? { structuredContent: data } : {}),
    isError,
  };
}

/** Turn a non-2xx API answer into an actionable tool error. */
function apiFailure({ status, data }) {
  const hints = {
    0: 'The site could not be reached.',
    401: 'The API key was rejected: BARIND_API_KEY must equal automation.apiKey in the server .env.',
    403: 'This machine\'s IP is not in automation.allowedIps on the server.',
    422: 'Validation failed; fix the listed fields and call again.',
  };
  // The API's own 404 carries {"error": "Not found"}; a route the site doesn't have answers with an empty body.
  hints[404] = data?.error
    ? 'No article with that id.'
    : 'The site has no such endpoint: the live code is older than this MCP server, or BARIND_API_URL is not the site\'s /api/v1.';
  const hint = hints[status] ?? (status >= 500 ? `Server error (HTTP ${status}).` : `HTTP ${status}.`);
  return reply(hint, { http_status: status, ...(data && typeof data === 'object' ? data : { body: data }) }, true);
}

const ok = (status) => status >= 200 && status < 300;

const readOnly = { readOnlyHint: true, openWorldHint: true };

/**
 * @param {import('./api.js').BarindApi} api
 */
export function createServer(api) {
  const server = new McpServer({ name: 'barind-post', version: '1.0.0' }, { instructions: INSTRUCTIONS });

  server.registerTool('list_categories', {
    title: 'List news categories',
    description: 'All site sections (id, name, slug). create_news needs one of these ids as category_id.',
    annotations: readOnly,
  }, async () => {
    const res = await api.categories();
    if (!ok(res.status)) return apiFailure(res);
    return reply(`${res.data.categories.length} categories.`, res.data);
  });

  server.registerTool('list_tags', {
    title: 'List tags',
    description: 'All existing tags (id, name). create_news accepts tag ids; unknown ids are skipped, tags cannot be created here.',
    annotations: readOnly,
  }, async () => {
    const res = await api.tags();
    if (!ok(res.status)) return apiFailure(res);
    return reply(`${res.data.tags.length} tags.`, res.data);
  });

  server.registerTool('check_source_url', {
    title: 'Check whether a source URL is already on the site',
    description: 'Look up an article by the URL of the story it was written from. Call this before writing an article from a source page to avoid duplicate work.',
    inputSchema: { source_url: z.string().url().describe('URL of the original source article') },
    annotations: readOnly,
  }, async ({ source_url }) => {
    const res = await api.exists(source_url);
    if (!ok(res.status)) return apiFailure(res);
    const summary = res.data.exists
      ? `Already on the site as #${res.data.id} (${res.data.status}).`
      : 'Not on the site yet.';
    return reply(summary, res.data);
  });

  server.registerTool('get_news', {
    title: 'Get an article',
    description: 'Status and basic fields of an article by id (e.g. to see whether a draft has been published by an editor).',
    inputSchema: { id: z.number().int().positive() },
    annotations: readOnly,
  }, async ({ id }) => {
    const res = await api.show(id);
    if (!ok(res.status)) return apiFailure(res);
    const data = { ...res.data, ...(res.data.status === 'published' ? { url: api.articleUrl(res.data.slug) } : {}) };
    return reply(`#${data.id} is ${data.status}.`, data);
  });

  server.registerTool('list_recent_news', {
    title: 'List recently published articles',
    description: 'The newest published articles with title, subtitle, key points, section and URL.',
    inputSchema: { limit: z.number().int().min(1).max(50).default(20) },
    annotations: readOnly,
  }, async ({ limit }) => {
    const res = await api.recent(limit);
    if (!ok(res.status)) return apiFailure(res);
    return reply(`${res.data.articles.length} recent articles.`, res.data);
  });

  server.registerTool('create_news', {
    title: 'Create a news article',
    description: 'Push one article to the live site. It is filed as a draft for the newsroom unless publish=true and the site '
      + 'allows auto-publishing; see the publish parameter. Rejected as a duplicate if the source_url, or the same title+body, already exists.',
    inputSchema: {
      title: z.string().trim().min(3).max(255).describe('Headline. Must be about what the body says.'),
      content: z.string().trim().min(20).describe(
        'Body as HTML paragraphs (<p>...</p>). Stick to p, h2-h4, strong, em, a, ul/ol/li and blockquote: '
        + 'the site editor strips custom classes and elements the next time the article is saved.'),
      category_id: z.number().int().positive().describe('A category id from list_categories'),
      subtitle: z.string().max(255).optional().describe('One-sentence standfirst; also used as the card excerpt and the SEO/social description'),
      key_points: z.array(z.string().min(1)).max(6).optional().describe('2-4 short key points, shown as the "এক নজরে" box and on cards without a photo'),
      language: z.enum(['bn', 'en']).optional().describe('Defaults to bn'),
      source: z.string().max(255).optional().describe('Name of the original outlet, e.g. "প্রথম আলো"'),
      source_url: z.string().url().max(500).optional().describe('URL of the original story. Required for auto-publishing; used for dedup.'),
      source_title: z.string().max(500).optional().describe('The headline as the source published it (used to spot the same event from other outlets)'),
      dateline: z.string().max(255).optional().describe('e.g. "রাজশাহী"'),
      suggested_image_url: z.string().url().max(500).optional().describe(
        'An image from the source page. It is only a suggestion an editor can adopt; it is never published as-is.'),
      tags: z.array(z.number().int().positive()).optional().describe('Tag ids from list_tags'),
      importance: z.number().int().min(0).max(5).optional().describe('0-5. At or above the server threshold (default 4) the article is featured on the home page.'),
      featured: z.boolean().optional().describe('Set featured explicitly instead of deriving it from importance'),
      publish: z.boolean().default(false).describe(
        'Ask for immediate publication. Honoured only if the site has automation.autoPublish on AND the article passes every '
        + 'publish gate (minimum word count, a subtitle or key points, a source_url, not a suspected duplicate, headline matches '
        + 'body, clean Bangla). Otherwise it is filed as a draft and the reasons are returned in publish_gates.'),
    },
    annotations: { readOnlyHint: false, destructiveHint: false, idempotentHint: false, openWorldHint: true },
  }, async ({ publish, ...fields }) => {
    const res = await api.createNews({ ...fields, status: publish ? 'published' : 'draft' });

    // A duplicate is an answer, not a failure: the story is already there, retrying won't help.
    if (res.status === 409) {
      const a = res.data.article ?? {};
      return reply(`Not created: ${res.data.error} (#${a.id}, ${a.status}).`, { outcome: 'duplicate', ...res.data });
    }
    if (!ok(res.status)) return apiFailure(res);

    const d = res.data;
    const published = d.status === 'published';
    const result = {
      outcome: published ? 'published' : 'draft',
      ...d,
      ...(published ? { url: api.articleUrl(d.slug) } : {}),
      admin_url: api.adminEditUrl(d.id),
    };
    let summary = published
      ? `Published #${d.id}: ${result.url}`
      : `Filed #${d.id} as a draft for the newsroom (/admin/incoming).`;
    if (publish && !published && d.publish_gates?.length) summary += ` Not published because: ${d.publish_gates.join('; ')}.`;
    if (d.possible_duplicate) summary += ` Flagged as a possible duplicate of #${d.possible_duplicate.id}.`;
    if (d.skipped_tags?.length) summary += ` Unknown tag ids skipped: ${d.skipped_tags.join(', ')}.`;
    return reply(summary, result);
  });

  return server;
}
