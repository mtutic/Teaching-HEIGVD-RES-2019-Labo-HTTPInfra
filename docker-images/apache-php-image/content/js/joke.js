// Script used to print joke on the main page of our site
$(() => {
  console.log('Loading joke');

  // function that get one joke and print it on the screen
  function loadJoke() {
    $.getJSON('/api/joke/', (joke) => {
      console.log(joke);
      const jokeText = joke.body;

	  // select the HTML object with joke id to modify his text
      $('#joke').text(jokeText);
    });
  }

  // call the function every 6s
  loadJoke();
  setInterval(loadJoke, 6000);
});
