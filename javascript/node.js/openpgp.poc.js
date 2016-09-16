var openpgp = require('openpgp'); // use as CommonJS, AMD, ES6 module or via window.openpgp
var fs = require('fs');

openpgp.initWorker({ path:'openpgp.worker.js' }) // set the relative web worker path

openpgp.config.aead_protect = false;

// Two sets of public and private keys are defined in this forlder and loaded from the filesystem below
var path = "./tests/_data/openpgp/";


// Handles loading all the files from the file system. This is down in an asynchronous fashion, so we pass in a callback
// that gets run, once all 4 files are loaded.
function loadKeys(callback) {
	var keys = {person1: {pub: null, priv: null}, person2: {pub: null, priv: null}};
	var keysLoaded = 0;
	var keysNeeded = 4;

	function checkForCallback() {
		if (keysLoaded == keysNeeded) {
			callback(keys);
		}
	}

	loadFile(path + 'test.person1.pub.key', function(data) {
		keys['person1']['pub'] = data;
		keysLoaded++;
		checkForCallback();
	});

	loadFile(path + 'test.person1.key', function(data) {
		keys['person1']['priv'] = data;
		keysLoaded++;
		checkForCallback();
	});

	loadFile(path + 'test.person2.pub.key', function(data) {
		keys['person2']['pub'] = data;
		keysLoaded++;
		checkForCallback();
	});


	loadFile(path + 'test.person2.key', function(data) {
		keys['person2']['priv'] = data;
		keysLoaded++;
		checkForCallback();
	});

}

function loadFile(path,callback) {
	fs.readFile(path, 'utf8', function (err, data) {
		if (err) throw err;
		callback(data);
	});
}

loadKeys(function(keys) {

	openpgp.config.debug = false;

	var options = {
		data: 'my secret', // input as String (or Uint8Array)
		publicKeys: openpgp.key.readArmored(keys.person2.pub).keys  // for encryption
	};

	console.log("Secret: ", options.data);

	openpgp.encrypt(options).then(function(ciphertext) {

		var encrypted = ciphertext.data; // '-----BEGIN PGP MESSAGE ... END PGP MESSAGE-----'
		console.log("Cipher Text: ", encrypted);


		var tempKey = openpgp.key.readArmored(keys.person2.priv).keys[0];
		tempKey.decrypt('test2');

		options = {
			message: openpgp.message.readArmored(encrypted),     // parse armored message
			publicKeys: openpgp.key.readArmored(keys.person2.pub).keys,    // for verification (optional)
			privateKey: tempKey // for decryption
		};

		openpgp.decrypt(options).then(function(plaintext) {
			console.log("Clear Text: ", plaintext.data); // 'my secret'
		});

	});

});
