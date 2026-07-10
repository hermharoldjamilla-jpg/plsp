const dns = require("node:dns");

dns.setDefaultResultOrder("ipv4first");

const { MongoClient } = require("mongodb");

const uri = "mongodb+srv://kaiselnotokay_db_user:notokay@cluster0.ltydj0a.mongodb.net/plsp_monitoring?authSource=admin&retryWrites=true&w=majority";

async function run() {
    try {
        const client = new MongoClient(uri);

        await client.connect();

        console.log("CONNECTED SUCCESSFULLY");

        console.log(await client.db().admin().ping());

        await client.close();
    } catch (err) {
        console.error(err);
    }
}

run();