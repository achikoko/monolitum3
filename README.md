# 🪨 Monolitum 🪨

The 3rd version. Hope the last. Now it is a monorepo thanks to [monorepo-builder](https://github.com/symplify/monorepo-builder), 
and the minimum version of PHP is the **8.2**.

## How it works

Monolitum tries to bring the philosophy of declarative UIs (react(ish) frameworks, flutter, etc.) to php. 
Everything that is a [node](packages/monolitum-core/src/MNode.php) is appended to a parent one (except the root) and can have children as well.
When `Monolitum::execute(...)` is called with a node as a parameter, two phases are being executed:

- Phase one: **building**.
  In this phase, each node does the heavy work. Managers connect to database, validate users and fields, routers decide which node (or webpage) is appended as a child and UI is decided.
  Things can fail and nodes can be rebuilt to create a page according to each situation.

- Phase two: **executing**.
  In this phase, the already built tree is called again from the root node to be "executed". Typically, in this phase the page is rendered.
  It cannot fail, as once the rendering has begun, PHP doesn't let you reset it (for example to send other headers).
   
There is also the concept of *building stack* and *pushing* [objects](packages/monolitum-core/src/MObject.php) on the stack. 
In regular conditions, the object is given to the building parents in order, until one of them accepts it and does something with it. 
For example, we can try to find a service node (manager) that is located up in the node tree, or we can use the stack to set a redirection url which will be catched by another manager whose purpose is to hold the url and set it to the redirect header at execution phase.

## Documentation

https://www.reddit.com/r/ProgrammerHumor/comments/1dtf9rq

## To try it...
1. Find out a way to import this project into yours (note that it is not in _packagist.org_, I'm sorry). The way I use is to have both projects (Monolitum and the application) as siblings in a folder, and reference Monolitum from the main `composer.json` inside the array of "repositories" of type "path".
2. Create an `index.php` which calls `Monolitum::execute(...)`. You can try this snippet as a very basic hello world.
```php

use monolitum\core\Monolitum;
use monolitum\frontend\component\H;
use monolitum\frontend\HTMLPage;
use function monolitum\core\m;

require 'vendor/autoload.php';

Monolitum::execute(
    // Instance an HTMLPage as a root node.
    new HTMLPage(function (HTMLPage $it){
        // This function builds the page (and the rest of children).
        // Instance a html Header and **push** it into the building stack with M(),
        // so HTMLPage will catch it and append it to the body.
        M(new H(1, function (H $it){
            // Append the string into the header tag.
            $it->append("Hello, Monolitum!");
        }));
    })
);

```

I would like to create an example that uses _twitter bootstrap_ for styling, but there is a little bit of setup complexity and have to using _managers_ to be able to retrieve the `.js` and `.css` files.
Exactly, in Monolitum you can manage the whole application and resources from the `index.php`, avoiding any "public" folder.
 
## External licences

There is some copy-pasted code (mostly javascript) in order this framework to be self-contained.

### JQuery

Code of JQuery is used. Licence (MIT) is in this repo:

https://github.com/jquery/jquery

### Bootstrap

Code of Twitter Bootstrap is used. Licence (MIT) is in this repo:

https://github.com/twbs/bootstrap

### Select2

Code of Select2 is used. Licence (MIT) is in this repo:

https://github.com/select2/select2

### Bootstrap Select2 Theme

Code of Bootstrap Select2 Theme is used. Licence (MIT) is in this repo:

https://github.com/apalfrey/select2-bootstrap-5-theme

### FontAwesome

Font Awesome is used. Licence (Free Icons: CC BY 4.0) is in this repo:

https://github.com/FortAwesome/Font-Awesome

### Quill (frontend and backend)

Code of quilljs is copied into this repository.
License (BSD-3-Clause) is in this repo:

https://github.com/quilljs/quill

Depends on Nadar's quill-delta-parser. License (MIT) is in this repo:

https://github.com/nadar/quill-delta-parser

### Carbon

Package "monolitum-i18n" depends on Carbon for translating dates. License (MIT) is in this repo:

https://github.com/CarbonPHP/carbon

### PHPMailer

Package "monolitum-mailer" depends on PHPMailer. Liscense (LGPL) is in this repo:

https://github.com/phpmailer/phpmailer

### Naucon/HtmlBuilder

Some code is based on Naucon's HtmlBuilder library. License (MIT) is in this repo:

https://github.com/naucon/HtmlBuilder
