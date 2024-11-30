const express = require('express');
const bodyParser = require('body-parser');
const { exec } = require('child_process');
const cors = require('cors'); // Include the CORS package
const app = express();
const port = 3002;
const host = "localhost";

// Middleware to parse request body
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

function sanitize(str){
  return str.replace( /[";'\[\]\{}()<>&/$\|]*/g,"");
}

app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
  next();
});

app.options('*', (req, res) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  res.sendStatus(200);
});

// Hashing APIs
app.post('/hash/:type', (req, res) => {
  const { type } = req.params;
  var { text } = req.body;
  
  text=sanitize(text);  
  const allowedHashes = ['md5', 'sha1'];

  if (!allowedHashes.includes(type)) {
    return res.status(400).send({error: "Invalid hash type. Use \"md5\" or \"sha1\"."});
  }
	const cmd = `echo -n "${text}" | openssl dgst -${type}`;
  console.log(cmd);
	
  exec(cmd,{shell: '/bin/bash'}, (error, stdout) => {
    if (error) {
      return res.status(500).send({error : "Error"});
    }
    res.send({ hash: stdout.split("= ")[1].trim() });
  });
});

// DES Encrypt API
app.post('/encrypt/des', (req, res) => {
  const { text, key } = req.body;

  text=sanitize(text)

  const cmd =`openssl des-ecb -e -K ${key} -in <(echo "${text}") -provider legacy -provider default -base64`;
  console.log(cmd);
  exec(cmd,{shell: '/bin/bash'}, (error, stdout) => {
    if (error) {
      return res.status(500).send({error : "Error"});
    }
    res.send({ encrypted: stdout.trim() });
  });
});

// DES Decrypt API
app.post('/decrypt/des', (req, res) => {
  const { text, key } = req.body;

  text=sanitize(text);

	const cmd =`openssl des-ecb -d -K ${key} -in <(echo "${text}") -provider legacy -provider default -base64`;
  console.log(cmd);
  exec(cmd,{shell: '/bin/bash'}, (error, stdout) => {
    if (error) {
      return res.status(500).send({error : "Error"});
    }
    res.send({ decrypted: stdout.trim() });
  });
});

// AES Encrypt API
app.post('/encrypt/aes', (req, res) => {
  const { text, key } = req.body;

  text=sanitize(text);

	const cmd =`echo -n "${text}" | openssl enc -aes-256-cbc -base64 -pass pass:"${key}" -iv 00000000000000000000000000000000`;
  console.log(cmd);
  exec(cmd,{shell: '/bin/bash'}, (error, stdout) => {
    if (error) {
      return res.status(500).send({error : "Error"});
    }
    res.send({ encrypted: stdout.trim() });
  });
});

// AES Decrypt API
app.post('/decrypt/aes', (req, res) => {
  const { text, key } = req.body;

  text=sanitize(text);

	const cmd =`echo "${text}" | openssl enc -aes-256-cbc -d -base64 -pass pass:"${key}" -iv 00000000000000000000000000000000`;
  console.log(cmd);
  exec(cmd,{shell: '/bin/bash'}, (error, stdout) => {
    if (error) {
      return res.status(500).send({error : "Error"});
    }
    res.send({ decrypted: stdout.trim() });
  });
});

// Old unused API, if you see this, you can remove it.
app.post('/flag', (req, res) => {
  const { text, key } = req.body;
    res.send({ flag: "flag_10 is d33c2f564583fe2c0nicetry074d4b35a07f4fc." });
});

// Start the server
app.listen(port, () => {
  console.log(`Crypto API running at http://${host}:${port}`);
});

