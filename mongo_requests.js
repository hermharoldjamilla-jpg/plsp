#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { MongoClient, ObjectId } = require('mongodb');

const action = process.argv[2] || 'fetch';
let payload = {};
const rawPayload = process.argv[3] || '';

const RETRY_COUNT = 1;
const RETRY_DELAY_MS = 500;
function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
function isTlsError(message) {
  return /SSL routines|tlsv1|tls|handshake|MongoServerSelectionError|serverSelection|ECONNRESET|ECONNREFUSED|ENOTFOUND|timed out/i.test(message || '');
}
async function connectWithRetry(client, retries = RETRY_COUNT) {
  try {
    return await client.connect();
  } catch (error) {
    if (retries > 0 && isTlsError(error.message)) {
      await sleep(RETRY_DELAY_MS);
      return connectWithRetry(client, retries - 1);
    }
    throw error;
  }
}

function parsePayload(raw) {
  if (!raw) return {};
  try {
    return JSON.parse(raw);
  } catch (err) {
    return {};
  }
}

function parseDotEnv(envPath) {
  if (!fs.existsSync(envPath)) return {};
  const lines = fs.readFileSync(envPath, 'utf8').split(/\r?\n/);
  const env = {};
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) continue;
    const idx = trimmed.indexOf('=');
    if (idx === -1) continue;
    const key = trimmed.slice(0, idx).trim();
    let value = trimmed.slice(idx + 1).trim();
    if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
      value = value.slice(1, -1);
    }
    env[key] = value;
  }
  return env;
}

function formatDate(value) {
  if (!value) return null;
  if (value instanceof Date) return value.toISOString();
  if (typeof value === 'string' && value.trim() !== '') return value;
  return null;
}

payload = parsePayload(rawPayload);
const envPath = path.join(__dirname, '.env');
const env = parseDotEnv(envPath);
const uri = process.env.MONGODB_URI || process.env.MONGO_URI || env.MONGODB_URI || env.MONGO_URI || '';
const dbName = process.env.MONGO_DB_NAME || env.MONGO_DB_NAME || 'plsp_monitoring';
const collectionName = process.env.MONGO_REQUESTS_COLLECTION || env.MONGO_REQUESTS_COLLECTION || 'request';

async function main() {
  if (!uri || uri.includes('<db_password>')) {
    process.stdout.write(JSON.stringify({ success: false, error: 'MongoDB URI is not configured.' }));
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
          { studentId: { $regex: search, $options: 'i' } },
          { student_name: { $regex: search, $options: 'i' } },
          { name: { $regex: search, $options: 'i' } },
          { teachers_id: { $regex: search, $options: 'i' } },
          { request_type: { $regex: search, $options: 'i' } },
          { description: { $regex: search, $options: 'i' } },
          { status: { $regex: search, $options: 'i' } },
          { admin_remarks: { $regex: search, $options: 'i' } },
        ];
      }
      const docs = await collection.find(query).sort({ request_date: -1, _id: -1 }).toArray();
      const rows = docs.map(doc => ({
        id: doc._id.toString(),
        studentId: doc.studentId || doc.student_id || doc.studentID || '',
        student_name: doc.student_name || doc.name || doc.full_name || doc.fullName || '',
        teachers_id: doc.teachers_id || doc.teacher_id || doc.teacherId || '',
        request_type: doc.request_type || doc.requestType || doc.type || '',
        description: doc.description || '',
        attachments: Array.isArray(doc.attachments) ? doc.attachments : doc.attachments ? [doc.attachments] : [],
        status: doc.status || 'pending',
        request_date: formatDate(doc.request_date || doc.requestDate || doc.createdAt || doc.created_at || null),
        response_date: formatDate(doc.response_date || doc.responseDate || null),
        admin_remarks: doc.admin_remarks || doc.adminRemarks || '',
      }));
      process.stdout.write(JSON.stringify(rows));
      return;
    }

    if (action === 'create') {
      const now = new Date();
      const doc = {
        studentId: payload.studentId || payload.student_id || payload.studentID || '',
        student_name: payload.student_name || payload.name || payload.full_name || payload.fullName || '',
        teachers_id: payload.teachers_id || payload.teacher_id || payload.teacherId || '',
        request_type: payload.request_type || payload.requestType || payload.type || '',
        description: payload.description || '',
        attachments: Array.isArray(payload.attachments) ? payload.attachments : payload.attachments ? [payload.attachments] : [],
        status: payload.status || 'pending',
        request_date: formatDate(payload.request_date || payload.requestDate || now.toISOString()),
        response_date: formatDate(payload.response_date || payload.responseDate || null),
        admin_remarks: payload.admin_remarks || payload.adminRemarks || '',
      };

      const result = await collection.insertOne(doc);
      process.stdout.write(JSON.stringify({ success: true, id: result.insertedId.toString() }));
      return;
    }

    if (action === 'update') {
      const id = payload.id;
      if (!ObjectId.isValid(id)) {
        process.stdout.write(JSON.stringify({ success: false, error: 'Invalid request id.' }));
        return;
      }

      const updateDoc = {};
      if (payload.message) {
        updateDoc.admin_remarks = payload.message;
      }
      const attachments = Array.isArray(payload.attachments) ? payload.attachments : payload.attachments ? [payload.attachments] : [];
      if (attachments.length > 0) {
        updateDoc.attachments = attachments;
      }

      if (Object.keys(updateDoc).length === 0) {
        process.stdout.write(JSON.stringify({ success: false, error: 'No update payload provided.' }));
        return;
      }

      const update = { $set: {} };
      if (updateDoc.admin_remarks !== undefined) {
        update.$set.admin_remarks = updateDoc.admin_remarks;
      }
      if (updateDoc.attachments) {
        update.$push = { attachments: { $each: updateDoc.attachments } };
      }

      const result = await collection.updateOne({ _id: new ObjectId(id) }, update);
      if (result.matchedCount === 0) {
        process.stdout.write(JSON.stringify({ success: false, error: 'Request not found.' }));
        return;
      }

      process.stdout.write(JSON.stringify({ success: true }));
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
