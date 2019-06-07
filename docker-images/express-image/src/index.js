// Include of express
const express = require('express');
const app = express();

// We use npm package to generate random jokes
const oneLinerJoke = require('one-liner-joke');

app.get('/', (req, res) => {
  res.send(oneLinerJoke.getRandomJoke());
});

app.listen(3000, () => {
  console.log('Accepting HTTP request on port 3000.');
});
