#!/usr/bin/env node
/**
 * Barind Post MCP server — lets local agents and tools push news to the live site.
 *
 *   node index.js           stdio transport (Claude Desktop / Claude Code / any MCP client that spawns it)
 *   node index.js --http    Streamable HTTP on MCP_HTTP_HOST:MCP_HTTP_PORT/mcp for other local processes
 *
 * Configuration comes from the environment or mcp-server/.env (see .env.example).
 * stdout belongs to the MCP protocol in stdio mode: log to stderr only.
 */
import { existsSync } from 'node:fs';
import { timingSafeEqual } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import express from 'express';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { StreamableHTTPServerTransport } from '@modelcontextprotocol/sdk/server/streamableHttp.js';
import { localhostHostValidation, hostHeaderValidation } from '@modelcontextprotocol/sdk/server/middleware/hostHeaderValidation.js';
import { BarindApi } from './src/api.js';
import { createServer } from './src/server.js';

const envFile = join(dirname(fileURLToPath(import.meta.url)), '.env');
if (existsSync(envFile)) process.loadEnvFile(envFile);

const fail = (message) => {
  console.error(`barind-post-mcp: ${message}`);
  process.exit(1);
};

const apiUrl = process.env.BARIND_API_URL ?? '';
const apiKey = process.env.BARIND_API_KEY ?? '';
if (!/^https?:\/\//.test(apiUrl)) fail('BARIND_API_URL must be set, e.g. https://barindpost.com/api/v1');
if (apiKey === '') fail('BARIND_API_KEY must be set to the server\'s automation.apiKey');
if (apiUrl.startsWith('http://') && !/^http:\/\/(localhost|127\.0\.0\.1)(:|\/)/.test(apiUrl)) {
  fail('BARIND_API_URL must use https:// — the API key would otherwise travel in clear text');
}

const api = new BarindApi({ baseUrl: apiUrl, apiKey, timeoutMs: Number(process.env.BARIND_API_TIMEOUT_MS) || 30_000 });

if (process.argv.includes('--http') || process.env.MCP_TRANSPORT === 'http') {
  startHttp();
} else {
  await createServer(api).connect(new StdioServerTransport());
  console.error(`barind-post-mcp: stdio, pushing to ${api.baseUrl}`);
}

function startHttp() {
  const host = process.env.MCP_HTTP_HOST || '127.0.0.1';
  const port = Number(process.env.MCP_HTTP_PORT) || 3333;
  const token = process.env.MCP_HTTP_TOKEN ?? '';
  const loopback = ['127.0.0.1', 'localhost', '::1'].includes(host);

  // Anyone who can reach this port can publish to the live site, so it needs its own
  // credential the moment it listens beyond this machine.
  if (!loopback && token === '') fail(`MCP_HTTP_TOKEN is required when MCP_HTTP_HOST is ${host}`);

  const app = express();
  if (loopback) {
    app.use(localhostHostValidation()); // DNS-rebinding protection: a web page cannot drive the server
  } else if (process.env.MCP_HTTP_ALLOWED_HOSTS) {
    app.use(hostHeaderValidation(process.env.MCP_HTTP_ALLOWED_HOSTS.split(',').map((h) => h.trim()).filter(Boolean)));
  }

  if (token !== '') {
    const expected = Buffer.from(`Bearer ${token}`);
    app.use((req, res, next) => {
      const given = Buffer.from(req.get('authorization') ?? '');
      if (given.length === expected.length && timingSafeEqual(given, expected)) return next();
      res.status(401).json({ jsonrpc: '2.0', error: { code: -32001, message: 'Invalid or missing MCP_HTTP_TOKEN' }, id: null });
    });
  }

  // Article HTML can outgrow express's 100 kB default.
  app.use(express.json({ limit: '5mb' }));

  // Stateless: a fresh server + transport per request, nothing to clean up between calls.
  app.post('/mcp', async (req, res) => {
    const server = createServer(api);
    const transport = new StreamableHTTPServerTransport({ sessionIdGenerator: undefined, enableJsonResponse: true });
    res.on('close', () => {
      transport.close();
      server.close();
    });
    try {
      await server.connect(transport);
      await transport.handleRequest(req, res, req.body);
    } catch (err) {
      console.error('barind-post-mcp:', err);
      if (!res.headersSent) res.status(500).json({ jsonrpc: '2.0', error: { code: -32603, message: 'Internal server error' }, id: null });
    }
  });

  const notAllowed = (req, res) => res.status(405).set('Allow', 'POST').json({ jsonrpc: '2.0', error: { code: -32000, message: 'Method not allowed' }, id: null });
  app.get('/mcp', notAllowed);
  app.delete('/mcp', notAllowed);

  app.listen(port, host, (err) => {
    if (err) fail(err.message);
    console.error(`barind-post-mcp: http://${host.includes(':') ? `[${host}]` : host}:${port}/mcp, pushing to ${api.baseUrl}`);
  });
}
