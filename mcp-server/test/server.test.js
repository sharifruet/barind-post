import { test, before, after, beforeEach } from 'node:test';
import assert from 'node:assert/strict';
import { createServer as createHttpServer } from 'node:http';
import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { InMemoryTransport } from '@modelcontextprotocol/sdk/inMemory.js';
import { BarindApi } from '../src/api.js';
import { createServer } from '../src/server.js';

// A stand-in for the site's /api/v1: records every request, answers with whatever the test queued.
let requests = [];
let nextResponse;
let fake;
let baseUrl;

before(async () => {
  fake = createHttpServer((req, res) => {
    let body = '';
    req.on('data', (c) => { body += c; });
    req.on('end', () => {
      requests.push({ method: req.method, url: req.url, auth: req.headers.authorization, body: body ? JSON.parse(body) : null });
      const { status = 200, json, headers = {} } = nextResponse;
      res.writeHead(status, { 'Content-Type': 'application/json', ...headers });
      res.end(json === undefined ? '' : JSON.stringify(json));
    });
  });
  await new Promise((resolve) => fake.listen(0, '127.0.0.1', resolve));
  baseUrl = `http://127.0.0.1:${fake.address().port}/api/v1`;
});

after(() => fake.close());

beforeEach(() => {
  requests = [];
  nextResponse = { status: 200, json: {} };
});

async function connect() {
  const server = createServer(new BarindApi({ baseUrl, apiKey: 'secret' }));
  const client = new Client({ name: 'test', version: '1.0.0' });
  const [a, b] = InMemoryTransport.createLinkedPair();
  await Promise.all([server.connect(a), client.connect(b)]);
  return client;
}

const article = { title: 'রাজশাহীতে নতুন সেতু চালু', content: '<p>রাজশাহীতে আজ নতুন সেতুর উদ্বোধন করা হয়েছে।</p>', category_id: 3 };

test('lists the tools a pushing client needs', async () => {
  const client = await connect();
  const { tools } = await client.listTools();
  assert.deepEqual(tools.map((t) => t.name).sort(),
    ['check_source_url', 'create_news', 'get_news', 'list_categories', 'list_recent_news', 'list_tags']);
});

test('list_categories sends the bearer key to /categories', async () => {
  nextResponse = { json: { categories: [{ id: 3, name: 'রাজশাহী', slug: 'rajshahi' }] } };
  const client = await connect();
  const result = await client.callTool({ name: 'list_categories', arguments: {} });
  assert.equal(result.isError, false);
  assert.deepEqual(requests[0], { method: 'GET', url: '/api/v1/categories', auth: 'Bearer secret', body: null });
  assert.equal(result.structuredContent.categories[0].id, 3);
});

test('create_news files a draft by default and links to the editor', async () => {
  nextResponse = { status: 201, json: { id: 41, slug: 'abc123', status: 'draft', skipped_tags: [], possible_duplicate: null, publish_gates: [] } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: { ...article, key_points: ['এক', 'দুই'] } });

  assert.equal(requests[0].method, 'POST');
  assert.equal(requests[0].url, '/api/v1/news');
  assert.equal(requests[0].body.status, 'draft');
  assert.deepEqual(requests[0].body.key_points, ['এক', 'দুই']);
  assert.equal('publish' in requests[0].body, false);

  assert.equal(result.isError, false);
  assert.equal(result.structuredContent.outcome, 'draft');
  assert.equal(result.structuredContent.admin_url, `${baseUrl.replace('/api/v1', '')}/admin/news/edit/41`);
  assert.equal(result.structuredContent.url, undefined);
});

test('publish=true is a request; failed gates come back as a draft with reasons', async () => {
  nextResponse = { status: 201, json: { id: 42, slug: 'def456', status: 'draft', skipped_tags: [], possible_duplicate: null, publish_gates: ['body is 9 words, minimum is 120'] } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: { ...article, publish: true } });

  assert.equal(requests[0].body.status, 'published');
  assert.equal(result.isError, false);
  assert.equal(result.structuredContent.outcome, 'draft');
  assert.match(result.content[0].text, /Not published because: body is 9 words/);
});

test('a published article returns its public URL', async () => {
  nextResponse = { status: 201, json: { id: 43, slug: 'ghi789', status: 'published', skipped_tags: [], possible_duplicate: null, publish_gates: [] } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: { ...article, publish: true } });
  assert.equal(result.structuredContent.outcome, 'published');
  assert.equal(result.structuredContent.url, `${baseUrl.replace('/api/v1', '')}/news/ghi789`);
});

test('a duplicate is reported as an outcome, not an error', async () => {
  nextResponse = { status: 409, json: { error: 'An article with this source_url already exists', article: { id: 7, slug: 'x', status: 'published' } } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: { ...article, source_url: 'https://example.com/a' } });
  assert.equal(result.isError, false);
  assert.equal(result.structuredContent.outcome, 'duplicate');
  assert.equal(result.structuredContent.article.id, 7);
});

test('server validation errors are tool errors with the field messages', async () => {
  nextResponse = { status: 422, json: { error: 'Validation failed', fields: { category_id: 'Category does not exist.' } } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: article });
  assert.equal(result.isError, true);
  assert.equal(result.structuredContent.fields.category_id, 'Category does not exist.');
});

test('a bad key gets a hint about automation.apiKey', async () => {
  nextResponse = { status: 401, json: { status: 401, error: 'Invalid or missing API key' } };
  const client = await connect();
  const result = await client.callTool({ name: 'list_tags', arguments: {} });
  assert.equal(result.isError, true);
  assert.match(result.content[0].text, /automation\.apiKey/);
});

test('a missing article and a missing endpoint get different 404 hints', async () => {
  const client = await connect();
  nextResponse = { status: 404, json: { error: 'Not found' } };
  const article404 = await client.callTool({ name: 'get_news', arguments: { id: 5 } });
  assert.match(article404.content[0].text, /^No article with that id/);

  nextResponse = { status: 404, json: '' }; // what the live site sends for a route it doesn't have
  const route404 = await client.callTool({ name: 'list_recent_news', arguments: {} });
  assert.match(route404.content[0].text, /^The site has no such endpoint/);
});

test('a redirect is surfaced instead of followed (it would turn the POST into a GET)', async () => {
  nextResponse = { status: 301, headers: { Location: 'https://www.example.com/api/v1/news' } };
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: article });
  assert.equal(requests.length, 1);
  assert.equal(result.isError, true);
  assert.match(result.content[0].text, /redirected to https:\/\/www\.example\.com/);
});

test('input that cannot pass the server rules is rejected before any request', async () => {
  const client = await connect();
  const result = await client.callTool({ name: 'create_news', arguments: { title: 'ab', content: 'short', category_id: 3 } });
  assert.equal(result.isError, true);
  assert.equal(requests.length, 0);
});

test('an unreachable site is a tool error, not a crash', async () => {
  const server = createServer(new BarindApi({ baseUrl: 'http://127.0.0.1:1/api/v1', apiKey: 'k' }));
  const client = new Client({ name: 'test', version: '1.0.0' });
  const [a, b] = InMemoryTransport.createLinkedPair();
  await Promise.all([server.connect(a), client.connect(b)]);
  const result = await client.callTool({ name: 'list_categories', arguments: {} });
  assert.equal(result.isError, true);
  assert.match(result.content[0].text, /could not be reached/);
});
