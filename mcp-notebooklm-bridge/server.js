/**
 * MCP Server Bridge PRO — Antigravity ↔ NotebookLM
 *
 * Uses playwright-core + existing Edge browser to automate
 * Google NotebookLM's web UI with PRO features:
 *   - query_notebook (Grounding)
 *   - notebooklm_deep_research
 *   - notebooklm_add_source (URL/YouTube)
 *   - list_notebook_sources
 *   - list_notebooks
 *   - create_notebook
 *
 * Transport: stdio
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { chromium } from "playwright-core";
import path from "path";
import { fileURLToPath } from "url";
import fs from "fs";

// ─── Config ────────────────────────────────────────────────────────
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const USER_DATA_DIR = path.join(__dirname, "user_data");
const NOTEBOOK_URL =
  process.env.NOTEBOOK_URL || "https://notebooklm.google.com";
const EDGE_PATH =
  process.env.BROWSER_PATH ||
  "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe";
const COOKIES_FILE = path.join(__dirname, "cookies.txt");

// ─── Playwright browser (lazy-initialized) ────────────────────────
let context = null;
let page = null;

async function ensureBrowser() {
  if (page && !page.isClosed()) return page;

  context = await chromium.launchPersistentContext(USER_DATA_DIR, {
    executablePath: EDGE_PATH,
    headless: false,
    args: ["--disable-blink-features=AutomationControlled", "--no-sandbox"],
    viewport: { width: 1280, height: 900 },
    ignoreDefaultArgs: ["--enable-automation"],
  });

  // Inject cookies if cookies.txt exists
  if (fs.existsSync(COOKIES_FILE)) {
    try {
      const cookieData = fs.readFileSync(COOKIES_FILE, "utf-8");
      // Basic Netscape cookies.txt parser
      const cookies = cookieData
        .split("\n")
        .filter((line) => line && !line.startsWith("#"))
        .map((line) => {
          const parts = line.split("\t");
          if (parts.length < 7) return null;
          return {
            name: parts[5],
            value: parts[6].trim(),
            domain: parts[0],
            path: parts[2],
            expires: parseInt(parts[4]),
            secure: parts[1] === "TRUE",
            httpOnly: false,
          };
        })
        .filter((c) => c !== null);
      await context.addCookies(cookies);
      console.error("✅ Cookies injected from cookies.txt");
    } catch (err) {
      console.error("❌ Error parsing cookies.txt:", err.message);
    }
  }

  page = context.pages()[0] || (await context.newPage());
  await page.goto(NOTEBOOK_URL, { waitUntil: "networkidle", timeout: 60000 });
  await page.waitForTimeout(3000);

  return page;
}

// ─── NotebookLM PRO Helpers ────────────────────────────────────────

/**
 * Trigger Deep Research.
 * Looks for the "Deep Research" toggle or button.
 */
async function triggerDeepResearch(prompt) {
  const p = await ensureBrowser();

  // 1. Enter the prompt
  const inputSelectors = [
    'textarea[aria-label*="query"]',
    'textarea[aria-label*="Ask"]',
    'textarea',
  ];
  let input = null;
  for (const sel of inputSelectors) {
    input = await p.$(sel);
    if (input) break;
  }
  if (!input) throw new Error("Chat input not found");

  await input.click();
  await input.fill(prompt);

  // 2. Look for Deep Research toggle/button
  const drSelectors = [
    'button[aria-label*="Deep Research"]',
    'span:has-text("Deep Research")',
    '[class*="deep-research"]',
    'button:has-text("Research")',
  ];

  let drButton = null;
  for (const sel of drSelectors) {
    drButton = await p.$(sel);
    if (drButton) break;
  }

  if (drButton) {
    await drButton.click();
    await p.waitForTimeout(1000);
  }

  // 3. Send
  await input.press("Enter");

  // 4. Wait for generation (Longer for Deep Research)
  await p.waitForTimeout(5000);
  const maxWait = 180000; // 3 minutes for deep research
  const startTime = Date.now();

  while (Date.now() - startTime < maxWait) {
    const isLoading = await p.evaluate(() => {
      const selectors = ['[class*="loading"]', 'mat-progress-bar', '[class*="spinner"]'];
      for (const sel of selectors) {
        const el = document.querySelector(sel);
        if (el && el.offsetParent !== null) return true;
      }
      return false;
    });
    if (!isLoading) break;
    await p.waitForTimeout(2000);
  }

  // 5. Extract results
  return await p.evaluate(() => {
    const el = document.querySelector('[class*="response"]:last-child, [class*="markdown"]:last-child');
    return el ? el.textContent.trim() : "Research completed. Please check the browser.";
  });
}

/**
 * Add a source (URL, PDF, YouTube).
 */
async function addSource(sourceType, value) {
  const p = await ensureBrowser();

  // 1. Click "Add Source" (+ button)
  const addBtnSelectors = [
    'button[aria-label*="Add source"]',
    'button:has([class*="plus"])',
    '[class*="add-source"]',
    'button:has-text("Add")',
  ];
  let addBtn = null;
  for (const sel of addBtnSelectors) {
    addBtn = await p.$(sel);
    if (addBtn) break;
  }
  if (!addBtn) throw new Error("Add Source button not found");
  await addBtn.click();
  await p.waitForTimeout(1000);

  // 2. Select internal source type
  if (sourceType === "url" || sourceType === "youtube") {
    const linkBtn = await p.$('button:has-text("Link"), button:has-text("Website")');
    if (linkBtn) await linkBtn.click();
    await p.waitForTimeout(500);
    const input = await p.$('input[type="url"], input[placeholder*="http"]');
    if (input) {
      await input.fill(value);
      await input.press("Enter");
    }
  } else if (sourceType === "text") {
    const textBtn = await p.$('button:has-text("Copied text")');
    if (textBtn) {
      await textBtn.click();
      const area = await p.$('textarea');
      if (area) {
        await area.fill(value);
        const submit = await p.$('button:has-text("Insert"), button:has-text("Add")');
        if (submit) await submit.click();
      }
    }
  }

  await p.waitForTimeout(3000);
  return `Source addition requested for ${sourceType}: ${value}`;
}

/**
 * List all notebooks (navigates to "Home").
 */
async function listNotebooks() {
  const p = await ensureBrowser();

  // Navigate to home if needed
  if (!p.url().includes("/notebooks") && p.url() !== "https://notebooklm.google.com/") {
    await p.goto("https://notebooklm.google.com/", { waitUntil: "networkidle" });
  }

  await p.waitForTimeout(2000);

  const notebooks = await p.evaluate(() => {
    const items = document.querySelectorAll('[class*="notebook-card"], [class*="NotebookCard"]');
    const results = [];
    items.forEach((item, i) => {
      const title = item.querySelector('[class*="title"]')?.textContent.trim() || `Notebook ${i}`;
      const url = item.querySelector('a')?.href || "";
      results.push({ index: i, title, url });
    });
    return results;
  });

  return notebooks;
}

/**
 * Create a new notebook.
 */
async function createNotebook(title) {
  const p = await ensureBrowser();

  // Navigate to home
  if (!p.url().includes("/notebooks") && p.url() !== "https://notebooklm.google.com/") {
    await p.goto("https://notebooklm.google.com/", { waitUntil: "networkidle" });
  }

  await p.waitForTimeout(2000);

  // Click "New Notebook"
  const newBtnSelectors = [
    'button:has-text("New notebook")',
    'button[aria-label*="New"]',
    '[class*="new-notebook"]',
  ];
  let newBtn = null;
  for (const sel of newBtnSelectors) {
    newBtn = await p.$(sel);
    if (newBtn) break;
  }
  if (!newBtn) throw new Error("New Notebook button not found");
  await newBtn.click();

  await p.waitForTimeout(3000);

  // Rename if possible (NotebookLM often opens a "Untitled" notebook)
  return `New notebook created. Please check the browser to manage it.`;
}

// ─── Existing Helpers ──────────────────────────────────────────────

async function listSources() {
  const p = await ensureBrowser();
  return await p.evaluate(() => {
    const results = [];
    const sourceElements = document.querySelectorAll(
      '[data-test-id="source-item"], .source-item, [class*="source"] [class*="title"], mat-list-item'
    );
    sourceElements.forEach((el, i) => {
      results.push({ index: i, title: el.textContent?.trim() || `Source ${i + 1}` });
    });
    return results;
  });
}

async function queryNotebook(prompt) {
  const p = await ensureBrowser();
  const input = await p.$('textarea[aria-label*="query"], textarea');
  if (!input) throw new Error("Input not found");
  await input.fill(prompt);
  await input.press("Enter");
  await p.waitForTimeout(5000);
  return await p.evaluate(() => {
    const el = document.querySelector('[class*="response"]:last-child');
    return el ? el.textContent.trim() : "Response captured. Check browser.";
  });
}

// ─── MCP Server Setup ──────────────────────────────────────────────

const server = new Server(
  { name: "notebooklm-bridge-pro", version: "2.0.0" },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    {
      name: "query_notebook",
      description: "Ask a question based on current sources (Grounding).",
      inputSchema: {
        type: "object",
        properties: { prompt: { type: "string" } },
        required: ["prompt"],
      },
    },
    {
      name: "notebooklm_deep_research",
      description: "Trigger a Deep Research task on the current notebook documents.",
      inputSchema: {
        type: "object",
        properties: { prompt: { type: "string", description: "The deep research query." } },
        required: ["prompt"],
      },
    },
    {
      name: "notebooklm_add_source",
      description: "Add a new source to the notebook.",
      inputSchema: {
        type: "object",
        properties: {
          type: { type: "string", enum: ["url", "youtube", "text"], description: "Source type" },
          value: { type: "string", description: "URL or text content" },
        },
        required: ["type", "value"],
      },
    },
    {
      name: "list_notebook_sources",
      description: "List all documents in the current notebook.",
      inputSchema: { type: "object", properties: {}, required: [] },
    },
    {
      name: "list_notebooks",
      description: "List all notebooks in the account.",
      inputSchema: { type: "object", properties: {}, required: [] },
    },
    {
      name: "create_notebook",
      description: "Create a new blank notebook.",
      inputSchema: { type: "object", properties: { title: { type: "string" } }, required: ["title"] },
    },
  ],
}));

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;
  try {
    switch (name) {
      case "query_notebook":
        return { content: [{ type: "text", text: await queryNotebook(args.prompt) }] };
      case "notebooklm_deep_research":
        return { content: [{ type: "text", text: await triggerDeepResearch(args.prompt) }] };
      case "notebooklm_add_source":
        return { content: [{ type: "text", text: await addSource(args.type, args.value) }] };
      case "list_notebook_sources":
        return { content: [{ type: "text", text: JSON.stringify(await listSources(), null, 2) }] };
      case "list_notebooks":
        return { content: [{ type: "text", text: JSON.stringify(await listNotebooks(), null, 2) }] };
      case "create_notebook":
        return { content: [{ type: "text", text: await createNotebook(args.title) }] };
      default:
        return { content: [{ type: "text", text: `Tool ${name} not implemented` }], isError: true };
    }
  } catch (error) {
    return { content: [{ type: "text", text: `Error: ${error.message}` }], isError: true };
  }
});

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("🚀 NotebookLM PRO Bridge started");
}

main().catch((err) => { console.error("Fatal:", err); process.exit(1); });
