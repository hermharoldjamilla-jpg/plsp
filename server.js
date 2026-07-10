require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const path = require('path');
const { execFile } = require('child_process');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());

// MongoDB connection (optional for now)
const dbURI = process.env.MONGO_URI || 'mongodb+srv://kaiselnotokay_db_user:kaiselnotokay_db_user@cluster0.ltydj0a.mongodb.net/myDatabase?retryWrites=true&w=majority';

mongoose.connect(dbURI)
  .then(() => console.log('✅ Successfully connected to MongoDB Atlas!'))
  .catch((error) => console.error('❌ MongoDB connection error:', error));

function renderPhp(res, phpFile, extraEnv = {}) {
  const env = { ...process.env, ...extraEnv };

  execFile('php', [phpFile], { cwd: __dirname, env }, (error, stdout, stderr) => {
    if (error) {
      console.error('PHP render error:', error.message);
      return res.status(500).send('Unable to render PHP page.');
    }

    if (stderr) {
      console.error('PHP stderr:', stderr);
    }

    res.type('html').send(stdout);
  });
}

// Serve the PHP landing page at the root URL
app.get('/', (req, res) => {
  renderPhp(res, path.join(__dirname, 'index.php'));
});

// Handle the PHP login form submission
app.post('/process_login.php', express.urlencoded({ extended: true }), (req, res) => {
  const phpFile = path.join(__dirname, 'process_login.php');
  const env = {
    REQUEST_METHOD: 'POST',
    QUERY_STRING: '',
  };

  const body = req.body || {};
  const formArgs = [];
  Object.entries(body).forEach(([key, value]) => {
    formArgs.push(`--${key}=${String(value)}`);
  });

  execFile('php', [phpFile, ...formArgs], { cwd: __dirname, env }, (error, stdout, stderr) => {
    if (error) {
      console.error('PHP POST error:', error.message);
      return res.status(500).send('Unable to process login.');
    }

    if (stderr) {
      console.error('PHP stderr:', stderr);
    }

    res.type('html').send(stdout);
  });
});

// 1. Define a quick Schema and Model for testing
const TestSchema = new mongoose.Schema({ message: String, date: { type: Date, default: Date.now } });
const TestModel = mongoose.model('TestData', TestSchema);

// 2. Create a POST route to accept data from your HTML page
app.post('/api/test-save', async (req, res) => {
  try {
    const newData = new TestModel({ message: req.body.text });
    await newData.save(); // This saves it to MongoDB Atlas!
    res.json({ success: true, savedData: newData });
  } catch (error) {
    res.status(500).json({ success: false, error: error.message });
  }
});

// Start Server
app.listen(PORT, () => {
  console.log(`🚀 Server is listening on http://localhost:${PORT}`);
});