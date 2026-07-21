const { MongoClient, ServerApiVersion } = require('mongodb');

const uri = "mongodb://kaiselnotokay_db_user:BKc50iPLpCXj8OYk@ac-wgpi9ud-shard-00-00.ltydj0a.mongodb.net:27017,ac-wgpi9ud-shard-00-01.ltydj0a.mongodb.net:27017,ac-wgpi9ud-shard-00-02.ltydj0a.mongodb.net:27017/plsp_monitoring?ssl=true&replicaSet=atlas-k92bpg-shard-0&authSource=admin&appName=Cluster0";

const client = new MongoClient(uri, {
  serverApi: {
    version: ServerApiVersion.v1,
    strict: true,
    deprecationErrors: true,
  }
});

async function run() {
  try {
    await client.connect();

    console.log("✅ CONNECTED SUCCESSFULLY");

    await client.db("admin").command({ ping: 1 });

    console.log("✅ Ping successful!");
  } catch (err) {
    console.error("❌ ERROR:");
    console.error(err);
  } finally {
    await client.close();
  }
}

run();