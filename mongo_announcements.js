#!/usr/bin/env node
const path = require('path');
const { MongoClient, ObjectId } = require('mongodb');
require('dotenv').config({ path: path.join(__dirname, '.env') });

const action = process.argv[2] || 'fetch';
let payload = {};
const rawPayload = process.argv[3] || '';

function parsePayload(raw) {
  if (!raw) return {};
  try {
    return JSON.parse(raw);
  } catch (err) {
    let clean = raw.trim();
    if (clean.startsWith("'") && clean.endsWith("'")) {
      clean = clean.slice(1, -1);
    }
    clean = clean.replace(/'/g, '"');
    clean = clean.replace(/([{,])\s*([A-Za-z0-9_]+)\s*:/g, '$1"$2":');
    clean = clean.replace(/:\s*([^\"\{\[\d\-\+][^,\}\n\r]*)(?=\s*(,|\}))/g, (match, value) => {
      const trimmed = value.trim();
      if (/^(null|true|false|[-+]?\d+(?:\.\d+)?(?:[eE][-+]?\d+)?)$/.test(trimmed)) {
        return ':' + trimmed;
      }
      return ':"' + trimmed.replace(/"/g, '\\"') + '"';
    });
    try {
      return JSON.parse(clean);
    } catch (err2) {
      console.error('Payload parse failed:', err2.message, 'raw:', raw, 'clean:', clean);
      return {};
    }
  }
}

payload = parsePayload(rawPayload);
const uri = process.env.MONGODB_URI || process.env.MONGO_URI || '';
const dbName = process.env.MONGO_DB_NAME || 'plsp_monitoring';
const collectionName = process.env.MONGO_ANNOUNCEMENT_COLLECTION || 'announcement';

const fs = require('fs');
const cachePath = path.join(__dirname, 'announcements_cache.json');

const RETRY_COUNT = 1;
const RETRY_DELAY_MS = 500;
function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
async function connectWithRetry(client, retries = RETRY_COUNT) {
  try {
    return await client.connect();
  } catch (error) {
    if (retries > 0 && /tls|ssl|alert/i.test(error.message)) {
      await sleep(RETRY_DELAY_MS);
      return connectWithRetry(client, retries - 1);
    }
    throw error;
  }
}

async function main() {
  if (!uri || uri.includes('<db_password>')) {
    console.error(JSON.stringify({ success: false, error: 'MongoDB URI is not configured.' }));
    process.exit(1);
  }

  const client = new MongoClient(uri, {
    serverSelectionTimeoutMS: 10000,
    connectTimeoutMS: 10000,
    socketTimeoutMS: 10000,
    maxPoolSize: 5,
    tls: true,
  });

  try {
    await connectWithRetry(client);
    const db = client.db(dbName);
    const collection = db.collection(collectionName);

    if (action === 'fetch') {
      const search = (payload.search || '').trim();
      const query = {};
      if (search) {
        query.$or = [
          { title: { $regex: search, $options: 'i' } },
          { description: { $regex: search, $options: 'i' } },
          { posted_by: { $regex: search, $options: 'i' } },
        ];
      }
      const docs = await collection.find(query).sort({ createdAt: -1, _id: -1 }).toArray();
      const rows = docs.map(doc => {
        let attachment = null;
        if (Array.isArray(doc.attachment)) {
          attachment = doc.attachment.length ? doc.attachment[0] : null;
        } else if (doc.attachment && typeof doc.attachment === 'object') {
          // handle stored object like { filename: 'a.png' }
          attachment = doc.attachment.filename || doc.attachment.name || null;
        } else {
          attachment = doc.attachment || null;
        }
        return {
          id: doc._id.toString(),
          title: doc.title || '',
          description: doc.description || '',
          createdAt: doc.createdAt || null,
          posted_by: doc.posted_by || 'Admin',
          attachment,
        };
      });
      // update local cache for offline fallback
      try { fs.writeFileSync(cachePath, JSON.stringify(rows), 'utf8'); } catch (e) { /* ignore cache write errors */ }
      process.stdout.write(JSON.stringify(rows));
      return;
    }

    if (action === 'create') {
      const now = new Date();
      const doc = {
        title: payload.title || '',
        description: payload.description || '',
        attachment: payload.attachment || null,
        createdAt: payload.createdAt || now.toISOString(),
        posted_by: payload.posted_by || 'Admin',
      };
      const result = await collection.insertOne(doc);
      process.stdout.write(JSON.stringify({ success: true, id: result.insertedId.toString() }));
      return;
    }

    if (action === 'delete') {
      const id = payload.id;
      const query = ObjectId.isValid(id) ? { _id: new ObjectId(id) } : { _id: id };
      const result = await collection.deleteOne(query);
      process.stdout.write(JSON.stringify({ success: result.deletedCount > 0 }));
      return;
    }

    process.stdout.write(JSON.stringify({ success: false, error: 'Unknown action.' }));
  } catch (error) {
    // On error (for example TLS/SSL to Atlas), attempt to return a local cache for 'fetch'
    try {
      if (action === 'fetch' && fs.existsSync(cachePath)) {
        const cached = fs.readFileSync(cachePath, 'utf8');
        // ensure cached is valid JSON
        JSON.parse(cached);
        process.stdout.write(cached);
        return;
      }
    } catch (e) {
      // fall through to error emit below
    }
    process.stdout.write(JSON.stringify({ success: false, error: error.message }));
  } finally {
    await client.close();
  }
}

main();
