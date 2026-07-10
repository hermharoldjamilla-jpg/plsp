#!/usr/bin/env node
const path = require('path');
const { MongoClient, ObjectId } = require('mongodb');
process.env.DOTENV_CONFIG_QUIET = 'true';
require('dotenv').config({ path: path.join(__dirname, '.env') });

const action = process.argv[2] || 'fetch';
const payload = process.argv[3] ? JSON.parse(process.argv[3]) : {};
const uri = process.env.MONGODB_URI || process.env.MONGO_URI || '';
const dbName = process.env.MONGO_DB_NAME || 'plsp_monitoring';
const collectionName = process.env.MONGO_ADMIN_COLLECTION || 'admin';

function formatDoc(doc) {
  return {
    id: doc._id?.toString() || '',
    dept: doc.dept || doc.department || '',
    username: doc.username || '',
    email: doc.email || '',
    password: doc.password || '',
    createdAt: doc.createdAt ? doc.createdAt.toISOString() : null,
    updatedAt: doc.updatedAt ? doc.updatedAt.toISOString() : null,
  };
}

(async () => {
  if (!uri || uri.includes('<db_password>')) {
    console.log(JSON.stringify({ success: false, error: 'MongoDB URI is not configured. Replace <db_password> with your real Atlas password.' }));
    process.exit(0);
  }

  const client = new MongoClient(uri, {
    serverSelectionTimeoutMS: 5000,
    connectTimeoutMS: 5000,
    socketTimeoutMS: 5000,
    maxPoolSize: 1,
  });

  try {
    await client.connect();
    const db = client.db(dbName);
    const collection = db.collection(collectionName);

    if (action === 'fetch') {
      const docs = await collection.find({}).sort({ createdAt: -1, _id: -1 }).toArray();
      await client.close();
      console.log(JSON.stringify(docs.map(formatDoc)));
      return;
    }

    if (action === 'create') {
      const doc = {
        dept: payload.dept || payload.department || '',
        username: payload.username || '',
        email: payload.email || '',
        password: payload.password || '',
        createdAt: new Date(),
        updatedAt: new Date(),
      };
      const result = await collection.insertOne(doc);
      const inserted = await collection.findOne({ _id: result.insertedId });
      await client.close();
      console.log(JSON.stringify({ success: true, item: formatDoc(inserted) }));
      return;
    }

    console.log(JSON.stringify({ success: false, error: `Unknown action: ${action}` }));
    await client.close();
  } catch (error) {
    try { await client.close(); } catch (closeError) {}
    console.log(JSON.stringify({ success: false, error: error.message }));
  }
})();