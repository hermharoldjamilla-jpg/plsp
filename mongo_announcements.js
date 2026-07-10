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

async function main() {
  if (!uri || uri.includes('<db_password>')) {
    console.error(JSON.stringify({ success: false, error: 'MongoDB URI is not configured.' }));
    process.exit(1);
  }

  const client = new MongoClient(uri, {
    serverSelectionTimeoutMS: 5000,
    connectTimeoutMS: 5000,
    socketTimeoutMS: 5000,
    maxPoolSize: 5,
  });

  try {
    await client.connect();
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
      const rows = docs.map(doc => ({
        id: doc._id.toString(),
        title: doc.title || '',
        description: doc.description || '',
        createdAt: doc.createdAt || null,
        posted_by: doc.posted_by || 'Admin',
        attachment: doc.attachment || null,
      }));
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
    process.stdout.write(JSON.stringify({ success: false, error: error.message }));
  } finally {
    await client.close();
  }
}

main();
