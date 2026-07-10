#!/usr/bin/env node
const path = require('path');
const bcrypt = require('bcryptjs');
const { MongoClient } = require('mongodb');
process.env.DOTENV_CONFIG_QUIET = 'true';
require('dotenv').config({ path: path.join(__dirname, '.env') });

function isConnectionError(message) {
  return /SSL routines|tlsv1|tls|handshake|MongoServerSelectionError|serverSelection|ECONNRESET|ECONNREFUSED|ENOTFOUND|timed out|querySrv/i.test(message || '');
}

function buildMongoUriCandidates(uri, dbName) {
  const candidates = [];
  const normalizedUri = String(uri || '').trim();
  if (!normalizedUri) {
    return candidates;
  }

  const addCandidate = (value) => {
    if (value && !candidates.includes(value)) {
      candidates.push(value);
    }
  };

  addCandidate(normalizedUri);

  try {
    const parsed = new URL(normalizedUri);
    const username = parsed.username ? decodeURIComponent(parsed.username) : '';
    const password = parsed.password ? decodeURIComponent(parsed.password) : '';
    const host = parsed.host || parsed.hostname || '';
    const cleanHost = host.replace(/:\d+$/, '');
    const authPart = username || password ? `${username}${password ? `:${password}` : ''}@` : '';
    const dbPath = parsed.pathname && parsed.pathname !== '/' ? parsed.pathname.replace(/^\//, '') : dbName;

    if (cleanHost) {
      addCandidate(`mongodb+srv://${authPart}${cleanHost}/${dbPath}?authSource=admin&retryWrites=true&w=majority&tls=true`);
      addCandidate(`mongodb+srv://${authPart}${cleanHost}/${dbPath}?authSource=admin&retryWrites=true&w=majority&tls=true&tlsAllowInvalidCertificates=true`);
      addCandidate(`mongodb://${authPart}${cleanHost}:27017/${dbPath}?authSource=admin&ssl=true`);
      addCandidate(`mongodb://${authPart}${cleanHost}:27017/${dbPath}?authSource=admin&tls=true`);
      addCandidate(`mongodb://${authPart}${cleanHost}:27017/${dbPath}?authSource=admin&tls=true&tlsAllowInvalidCertificates=true`);
    }
  } catch (error) {
    // Ignore URI parsing errors and fall back to the original URI.
  }

  return candidates;
}

async function connectWithFallback(uri, dbName, collectionName) {
  const candidates = buildMongoUriCandidates(uri, dbName);
  let lastError = null;

  for (const candidateUri of candidates) {
    const client = new MongoClient(candidateUri, {
      serverSelectionTimeoutMS: 10000,
      connectTimeoutMS: 10000,
      socketTimeoutMS: 10000,
      maxPoolSize: 1,
      tls: true,
      tlsAllowInvalidCertificates: true,
      retryWrites: true,
      retryReads: true,
    });

    try {
      await client.connect();
      const db = client.db(dbName);
      const collection = db.collection(collectionName);
      return { client, collection };
    } catch (error) {
      lastError = error;
      await client.close().catch(() => {});
    }
  }

  throw lastError || new Error('Unable to connect to MongoDB.');
}

function escapeRegExp(value) {
  return String(value ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function buildUserQuery(identifier, loginType) {
  const normalizedIdentifier = String(identifier ?? '').trim();
  if (!normalizedIdentifier) {
    return { _id: null };
  }

  if (loginType === 'admin') {
    return {
      $or: [
        { teachers_id: normalizedIdentifier },
        { teacher_id: normalizedIdentifier },
        { email: normalizedIdentifier },
        { admin_email: normalizedIdentifier },
      ],
    };
  }

  return {
    $or: [
      { studentId: normalizedIdentifier },
      { student_id: normalizedIdentifier },
      { studentNo: normalizedIdentifier },
      { student_no: normalizedIdentifier },
      { email: normalizedIdentifier },
      { gmail: normalizedIdentifier },
      { student_email: normalizedIdentifier },
      { email_address: normalizedIdentifier },
    ],
  };
}

function passwordMatches(storedPassword, enteredPassword) {
  const stored = String(storedPassword ?? '');
  const entered = String(enteredPassword ?? '');

  if (stored === '' || entered === '') {
    return false;
  }

  if (stored === entered) {
    return true;
  }

  if (/^\$2[aby]\$/i.test(stored)) {
    try {
      return bcrypt.compareSync(entered, stored);
    } catch (error) {
      return false;
    }
  }

  return false;
}

function normalizeUser(user, identifier, loginType) {
  if (!user || typeof user !== 'object') {
    return user;
  }

  const emailValue = user.email ?? user.gmail ?? user.student_email ?? user.studentEmail ?? user.email_address ?? user.email_contact ?? user.studentEmail ?? identifier;

  return {
    ...user,
    role: loginType,
    email: emailValue,
    gmail: user.gmail ?? user.email ?? user.student_email ?? user.studentEmail ?? user.email_address ?? user.email_contact ?? emailValue,
    student_id: user.student_id ?? user.studentId ?? user.student_no ?? user.studentNumber ?? user.studentNo ?? user.studentID ?? user.id ?? '',
    studentId: user.studentId ?? user.student_id ?? user.student_no ?? user.studentNumber ?? user.studentNo ?? user.studentID ?? user.id ?? '',
    name: user.name ?? user.full_name ?? user.student_name ?? '',
    program: user.program ?? user.course ?? user.course_of_study ?? '',
    department: user.department ?? user.dept ?? user.college ?? user.faculty ?? '',
  };
}

(async () => {
  const loginType = process.argv[2] || 'student';
  const identifier = process.argv[3] || '';
  const password = process.argv[4] || '';

  const uri = process.env.MONGODB_URI || process.env.MONGO_URI || '';
  const dbName = process.env.MONGO_DB_NAME || 'plsp_monitoring';
  const collectionName = loginType === 'admin' ? (process.env.MONGO_ADMIN_COLLECTION || 'admin') : (process.env.MONGO_STUDENTS_COLLECTION || 'students');

  if (!uri || uri.includes('<db_password>')) {
    process.stdout.write(JSON.stringify({ success: false, error: 'The authentication service is currently unavailable. Please try again later.' }));
    process.exit(0);
  }

  try {
    const { client, collection } = await connectWithFallback(uri, dbName, collectionName);

    const query = buildUserQuery(identifier, loginType);
    const user = await collection.findOne(query);
    await client.close();

    if (!user) {
      process.stdout.write(JSON.stringify({ success: false, error: 'User not found.' }));
      process.exit(0);
    }

    const normalizedUser = normalizeUser(user, identifier, loginType);
    const passwordCandidates = [normalizedUser.password, normalizedUser.password_hash, normalizedUser.hash, normalizedUser.passcode, normalizedUser.passwordHash].filter(Boolean);
    const passwordMatched = passwordCandidates.some((candidate) => passwordMatches(candidate, password));

    if (!passwordMatched) {
      process.stdout.write(JSON.stringify({ success: false, error: 'Invalid password.' }));
      process.exit(0);
    }

    process.stdout.write(JSON.stringify({ success: true, user: normalizedUser }));
  } catch (error) {
    const message = isConnectionError(error.message) ? 'The authentication service is currently unavailable. Please try again later.' : error.message;
    process.stdout.write(JSON.stringify({ success: false, error: message }));
  }
})();
