# Step 4: AJAX requests with JQuery

## Description

The aim of this step is to make our web page dynamically updated. In order to do that, we will print a joke every few seconds on the page.

To make this possible, we have to allow our static container (step 1) to receive a joke from the dynamic container (step 2) with an AJAX request made by JQuery. To do that, we will write a script and import it on our `index.html` page.

The sources of this part of the lab is distributed in several folder :

+ `index.html` can be found in the `docker-images/apache-php-image/content/` folder.
+ `joke.js`, the script that generate joke, can be found in the `docker-images/apache-php-image/content/js/` folder.

## Implementation

For this step, we just have to modify what we have already done in the previous steps.

Firstly, we add `vim` to our containers to be able to create and edit files inside of them. To do that, we just add to the Dockerfile the following lines :

```dockerfile
RUN apt-get update && \
  apt-get install -y vim
```

Now that our containers are ready, we have to indicate to our web page that we allow a specific script.

```html
<!-- Custom script to load a joke -->
<script src="js/joke.js"></script>
```

With these lines, we now allow the page to work with our script. It's time to make the script.

The script must be an AJAX request and for that we can use the JQuery library. The aim of this script is to call the container at the `/api/joke/` path to get a joke and use them to update a DOM element on the web page. For this, we use the AJAX request with the AJAX variable `$` and the function `getJSON` that can get the JSON send by the joke generator (step 2). After that we update the text on the web page with this line `$('#joke').text(jokeText);`. Finally, we will call this function every six second so we use the `setInterval` function.

```javascript
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
```

For this step, we have to modify the template that we use to make our web page. The template has a title and a subtitle and we won't modify this. So we decide to make a new text element and we add this in the `index.html`. As you can see, the new text element have the ID `joke` and this ID is get by the script to know where to update the text in the page by the label `#joke`.

```html
<h3 id="joke" class="text-white-50 mx-auto mt-2 mb-5">JOKE</h3>
```

## Usage

So if you have follow the step 1 with our documentation, you have to rebuild the static container's image and make a new container.

Than you can go on http://demo.res.ch:8080/ and see that you have a joke on your screen. Furthermore, this joke will change every six seconds.