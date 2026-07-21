const fs = require('fs');
const path = require('path');
const dir = path.join(__dirname, 'uploads');
if (!fs.existsSync(dir)) { console.error('uploads folder missing'); process.exit(1); }
fs.readdirSync(dir).forEach(f => {
  if (/\s/.test(f)) {
    const nf = f.replace(/\s+/g, '_');
    try {
      fs.renameSync(path.join(dir, f), path.join(dir, nf));
      console.log('RENAMED', f, '->', nf);
    } catch (e) {
      console.error('ERR', f, e.message);
    }
  }
});
console.log('done');
