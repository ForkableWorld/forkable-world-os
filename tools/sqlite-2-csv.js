const sqlite3 = require('sqlite3').verbose();
const fs = require('fs').promises;
const { Parser } = require('json2csv');

// Path to your SQLite file
const dbPath = 'C:\\Users\\User\\OneDrive\\code\\decentralized-fda\\apps\\fdai\\tests\\fixtures\\qm_test.sqlite';

// Open the SQLite Database
const db = new sqlite3.Database(dbPath, sqlite3.OPEN_READONLY, (err) => {
  if (err) {
    console.error(err.message);
  }
  console.log('Connected to the SQLite database.');
});

// Function to export table to CSV
async function exportTableToCSV(tableName) {
  return new Promise((resolve, reject) => {
    db.all(`SELECT * FROM ${tableName}`, [], async (err, rows) => {
      if (err) {
        return reject(err);
      }

      // Check if the table is empty
      if (rows.length === 0) {
        console.log(`Table ${tableName} is empty. Skipping CSV export.`);
        return resolve();
      }

      try {
        const json2csvParser = new Parser();
        const csv = json2csvParser.parse(rows);
        await fs.writeFile(`${tableName}.csv`, csv);
        console.log(`Exported ${tableName} to CSV.`);
        resolve();
      } catch (writeErr) {
        reject(writeErr);
      }
    });
  });
}

// Main function to export all tables
async function exportAllTables() {
  return new Promise((resolve, reject) => {
    db.all("SELECT name FROM sqlite_master WHERE type='table'", async (err, tables) => {
      if (err) {
        return reject(err);
      }

      try {
        for (const table of tables) {
          await exportTableToCSV(table.name);
        }
        resolve();
      } catch (exportErr) {
        reject(exportErr);
      }
    });
  });
}

// Execute the script
exportAllTables()
  .then(() => {
    console.log('All tables exported.');
  })
  .catch((err) => {
    console.error('An error occurred:', err);
  })
  .finally(() => {
    db.close();
    console.log('Closed the database connection.');
  });

