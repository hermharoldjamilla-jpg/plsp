#!/usr/bin/env node
const path = require('path');
const { MongoClient } = require('mongodb');
process.env.DOTENV_CONFIG_QUIET = 'true';
require('dotenv').config({ path: path.join(__dirname, '.env') });

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

function normalizeValue(value) {
  return String(value ?? '').trim();
}

function mergeLabels(preferred, actual) {
  const labels = [...preferred];
  for (const value of actual) {
    if (value && !labels.includes(value)) {
      labels.push(value);
    }
  }
  return labels;
}

function buildSeries(labels, countsByLabel) {
  return labels.map(label => countsByLabel[label] || 0);
}

(async () => {
  const uri = process.env.MONGODB_URI || process.env.MONGO_URI || '';
  const dbName = process.env.MONGO_DB_NAME || 'plsp_monitoring';
  const collectionName = process.env.MONGO_STUDENTS_COLLECTION || 'students';

  if (!uri) {
    process.stdout.write(JSON.stringify({ success: false, error: 'MongoDB URI is not configured.' }));
    process.exit(0);
  }

  const client = new MongoClient(uri, {
    serverSelectionTimeoutMS: 10000,
    connectTimeoutMS: 10000,
    socketTimeoutMS: 10000,
    maxPoolSize: 1,
    tls: true,
  });

  try {
    await connectWithRetry(client);
    const db = client.db(dbName);
    const students = db.collection(collectionName);

    const year1 = await students.countDocuments({ year_level: '1st Year' });
    const year2 = await students.countDocuments({ year_level: '2nd Year' });
    const year3 = await students.countDocuments({ year_level: '3rd Year' });
    const year4 = await students.countDocuments({ year_level: '4th Year' });
    const total = await students.countDocuments({});

    const regular = await students.countDocuments({ student_type: 'Regular' });

    const categoryDocs = await students.aggregate([
      { $group: { _id: '$circumstances_type', count: { $sum: 1 } } },
      { $sort: { _id: 1 } },
    ]).toArray();

    const departmentDocs = await students.aggregate([
      { $group: { _id: '$department', count: { $sum: 1 } } },
      { $sort: { _id: 1 } },
    ]).toArray();

    const preferredCategories = ['Solo Parent', 'PWD', 'Working Student', 'Irregular', 'Indigenous People', 'PHC'];
    const preferredDepartments = ['CCSE', 'COA', 'CTHM', 'CAS', 'CTED', 'CHK', 'CBAM'];

    const categoryLabels = mergeLabels(
      preferredCategories,
      categoryDocs.map(doc => normalizeValue(doc._id)).filter(Boolean)
    );
    const departmentLabels = mergeLabels(
      preferredDepartments,
      departmentDocs.map(doc => normalizeValue(doc._id)).filter(Boolean)
    );

    const categoryCountsByLabel = Object.fromEntries(
      categoryDocs.map(doc => [normalizeValue(doc._id), doc.count])
    );
    const departmentCountsByLabel = Object.fromEntries(
      departmentDocs.map(doc => [normalizeValue(doc._id), doc.count])
    );

    const categoryCounts = buildSeries(categoryLabels, categoryCountsByLabel);
    const departmentCounts = buildSeries(departmentLabels, departmentCountsByLabel);

    await client.close();

    process.stdout.write(JSON.stringify({
      success: true,
      year1,
      year2,
      year3,
      year4,
      total,
      regular,
      categoryLabels,
      categoryCounts,
      departmentLabels,
      departmentCounts,
    }));
  } catch (error) {
    await client.close().catch(() => {});
    process.stdout.write(JSON.stringify({ success: false, error: error.message }));
  }
})();
