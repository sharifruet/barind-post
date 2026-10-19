# Barind Post MCP server

A local [MCP](https://modelcontextprotocol.io) server that lets agents and other tools push news to the
live site. It is a thin client over the site's `/api/v1` automation API (`app/Controllers/Api/NewsController.php`),
which is the same API the n8n pipelines use. It never touches the database. Validation, dedup (`source_url`, content hash),
the auto-publish switch and every publish gate stay on the server, so an MCP client cannot do
anything n8n can't.

```
agent / app ──MCP──▶ mcp-server (this machine) ──HTTPS + Bearer key──▶ barindpost.com/api/v1
```

## Setup

Requires Node ≥ 20.12.

```bash
cd mcp-server
npm ci
cp .env.example .env      # set BARIND_API_URL and BARIND_API_KEY (= automation.apiKey on the server)
npm test                  # offline, uses a fake API
```

The production `.env` must have `automation.apiKey` and `automation.authorId` set, or every call fails
closed with 401/500. If `automation.allowedIps` is set, this machine's public IP must be in it.

## Running it

**stdio** is for clients that start the server themselves. For Claude Code:

```bash
claude mcp add barind-post -- node /absolute/path/to/barind-post/mcp-server/index.js
```

For Claude Desktop or any other client, use `claude_desktop_config.json` or the equivalent:

```json
{
  "mcpServers": {
    "barind-post": {
      "command": "node",
      "args": ["/absolute/path/to/barind-post/mcp-server/index.js"]
    }
  }
}
```

**HTTP** is for other local processes and services that connect to a running server:

```bash
npm run start:http        # http://127.0.0.1:3333/mcp (Streamable HTTP, stateless)
```

By default it listens only on loopback and rejects foreign `Host` headers. To expose it on a LAN, set
`MCP_HTTP_HOST=0.0.0.0` and `MCP_HTTP_TOKEN`, and optionally `MCP_HTTP_ALLOWED_HOSTS`. The server refuses to start
on a non-loopback address without a token, because anyone who can reach the port can post to the
live site. Clients then send `Authorization: Bearer <MCP_HTTP_TOKEN>`.

## Tools

| Tool | API call | Notes |
|---|---|---|
| `list_categories` | `GET /categories` | `create_news` needs a `category_id` |
| `list_tags` | `GET /tags` | tags can't be created here; unknown ids are skipped |
| `check_source_url` | `GET /news/exists` | call before writing from a source page |
| `create_news` | `POST /news` | see below |
| `get_news` | `GET /news/{id}` | e.g. has an editor published the draft yet? |
| `list_recent_news` | `GET /news/recent` | newest published articles |

`create_news` takes `title`, `content` (HTML paragraphs) and `category_id`, plus optional `subtitle`,
`key_points[]`, `source`, `source_url`, `source_title`, `dateline`, `suggested_image_url`, `tags[]`,
`importance` (0–5), `featured`, `language` and `publish`.

- **Draft by default.** The article lands in `/admin/incoming` for the newsroom.
- **`publish: true` is a request.** The server publishes only if `automation.autoPublish` is on and
  the article passes every gate in `publishGateFailures()`: word count, subtitle or key points, a `source_url`, not a
  suspected duplicate, headline grounded in the body, and clean Bangla. Otherwise it's filed as a draft and
  `publish_gates` says why. That is a normal result, not an error.
- **Duplicates** (HTTP 409) come back as `outcome: "duplicate"` with the existing article's id.
  They are not errors, so an agent won't retry them.
- There is deliberately no `image_url`. Pipelines must not set the article image. `suggested_image_url`
  is shown to the editor, who can adopt it.

Every result has a one-line summary for the model plus the full JSON (`structuredContent`), including
`url` once an article is published and `admin_url` for the editor.
