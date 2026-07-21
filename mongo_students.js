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

function escapeRegExp(value) {
  return String(value ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function buildFilter(type, search) {
  const filter = {};
  if (type && type !== 'All' && type !== '__counts__') {
    filter.circumstances_type = type;
  }
  if (search) {
    const regex = new RegExp(escapeRegExp(search), 'i');
    filter.$or = [
      { studentId: regex },
      { student_id: regex },
      { email: regex },
      { name: regex },
      { program: regex },
      { department: regex },
    ];
  }
  return filter;
}

(async () => {
  const mode = process.argv[2] || 'All';
  const search = process.argv[3] || '';
  const uri = process.env.MONGODB_URI || process.env.MONGO_URI || '';
  const dbName = process.env.MONGO_DB_NAME || 'plsp_monitoring';
  const collectionName = process.env.MONGO_STUDENTS_COLLECTION || 'students';

  if (!uri) {
    process.stdout.write(JSON.stringify({ error: 'MongoDB URI is not configured.' }));
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
    const collection = db.collection(collectionName);

    if (mode === '__counts__') {
      const types = ['PWD', 'Solo Parent', 'Irregular', 'Working Student', 'PHC'];
      const counts = {};
      for (const type of types) {
        counts[type] = await collection.countDocuments({ circumstances_type: type });
      }
      counts.All = await collection.countDocuments({});
      process.stdout.write(JSON.stringify(counts));
      return;
    }

    const filter = buildFilter(mode, search);
    const rows = await collection
      .find(filter)
      .project({
        _id: 0,
        studentId: 1,
        name: 1,
        program: 1,
        department: 1,
        year_level: 1,
        student_type: 1,
        circumstances_type: 1,
        email: 1,
        mobile: 1,
        contact: 1,
        contact_number: 1,
        phone: 1,
        address: 1,
        dob: 1,
        birthdate: 1,
        blood_type: 1,
        donor_status: 1,
        email_contact: 1,
        addr_contact: 1,
        ec_person: 1,
        ec_number: 1,
        ec_relationship: 1,
        ec_name: 1,
        relationship_with_ec: 1,
        contact_no_ec: 1,
        emergency_name: 1,
        emergency_contact: 1,
        emergency_relation: 1,
        emergency_address: 1,
        date_verified: 1,
        verified_by: 1,
      })
      .sort({ name: 1 })
      .toArray();

    const payload = rows.map((row) => ({
      student_id: String(row.studentId ?? row.student_id ?? '').trim(),
      name: String(row.name ?? '').trim(),
      program: String(row.program ?? '').trim(),
      department: String(row.department ?? '').trim(),
      year_level: String(row.year_level ?? '').trim(),
      student_type: String(row.student_type ?? '').trim(),
      type: String(row.circumstances_type ?? row.type ?? '').trim(),
      email: String(row.email ?? row.student_email ?? row.email_contact ?? '').trim(),
      contact: String(row.contact_number ?? row.mobile ?? row.contact ?? row.phone ?? '').trim(),
      address: String(row.address ?? row.emergency_address ?? '').trim(),
      dob: String(row.dob ?? row.birthdate ?? '').trim(),
      blood_type: String(row.blood_type ?? '').trim(),
      donor_status: String(row.donor_status ?? '').trim(),
      email_contact: String(row.email_contact ?? row.email ?? '').trim(),
      addr_contact: String(row.addr_contact ?? row.address ?? '').trim(),
      emrg_name: String(row.ec_name ?? row.ec_person ?? row.emergency_name ?? '').trim(),
      emrg_rel: String(row.relationship_with_ec ?? row.ec_relationship ?? row.emergency_relation ?? '').trim(),
      emrg_contact: String(row.contact_no_ec ?? row.ec_number ?? row.emergency_contact ?? '').trim(),
      date_verified: String(row.date_verified ?? '').trim(),
      verified_by: String(row.verified_by ?? '').trim(),
    }));

    process.stdout.write(JSON.stringify(payload));
  } catch (error) {
    await client.close().catch(() => {});
    process.stdout.write(JSON.stringify({ error: error.message }));
  } finally {
    await client.close().catch(() => {});
  }
})();
