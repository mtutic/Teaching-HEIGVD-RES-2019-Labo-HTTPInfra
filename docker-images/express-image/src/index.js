// Include of express
const express = require('express');

const app = express();

// We use npm package to generate random jokes
const oneLinerJoke = require('one-liner-joke');

// Handle get requests by sending jokes in a JSON payload
app.get('/', (req, res) => {
  console.log('sending a joke');
  res.send(oneLinerJoke.getRandomJoke());
});

// Start the server listening
app.listen(3000, () => {
  console.log('Accepting HTTP request on port 3000.');
});
