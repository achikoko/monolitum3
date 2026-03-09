# 🪨 Monolitum 🪨

The 3rd version. Hope the last. Now it is a monorepo thanks to [monorepo-builder](https://github.com/symplify/monorepo-builder), 
and the minimum version of PHP is the **8.2**.

## How it works

Monolitum tries to bring the philosophy of declarative UIs (react(ish) frameworks, flutter, etc.) to php. 
Everything that is a [node](packages/monolitum-core/src/MNode.php) is appended to a parent one (except the root) and can have children as well.
When `Monolitum::execute(...)` is called with a node as a parameter, two phases are being executed:

- Phase one: **building**.
  In this phase, each node does the heavy work. Managers connect to database, validate users and fields, routers decide which node (or webpage) is appended as a child.
  Things can fail and nodes can be rebuilt to create a page according to each situation.

- Phase two: **executing**.
  In this phase, the built tree is called again from the root node to be executed. Typically, in this phase the page is rendered.
  It cannot fail, as once the rendering has begun, php doesn't let you to reset it (for example to send other headers).
   
There is also a concept of *building stack* and *pushing* [objects](packages/monolitum-core/src/MObject.php) on the stack. 
In regular conditions, the object is given to the building parents in order, until one of them accepts it and does something. 
For example, to find a manager that is a node up in the tree. Or setting a redirection url to a manager before the page renderer 
which will set the redirect header (at execution phase) and will avoid executing the other renderer.

## Documentation

https://www.reddit.com/r/ProgrammerHumor/comments/1dtf9rq

## To try it...
1. Find a way to import this project into yours (note that it is not in _packagist.org_, sorry). My way is to have both projects as siblings in a folder, and
   reference monolitum from the main `composer.json` in the array of "repositories" of type "path".
2. Create an `index.php` which calls `Monolitum::execute(...)`. You can try this snippet as a very basic hello world.
```php

use monolitum\core\Monolitum;
use monolitum\frontend\component\H;
use monolitum\frontend\HTMLPage;
use function monolitum\core\m;

require 'vendor/autoload.php';

Monolitum::execute(
    "", // Ignore it.
    null, // Ignore it.
    // Instance an HTMLPage as a root node.
    new HTMLPage(function (HTMLPage $it){
        // This function builds the page (and the rest of children).
        // Instance a html Header and **push** it into the building stack with M(),
        // so HTMLPage will catch it and append it to the body.
        M(new H(1, function (H $it){
            // Append the string into the header tag.
            $it->append("Hello Monolitum!");
        }));
    })
);

```

I would like to create a bootstrap example, but there is a little bit of setup using _managers_ to be able to retrieve the `.js` and `.css` files.
Exactly, in monolitum you can manage the whole application and resources avoiding the "public" folder, and starting always from the `index.php`.
 
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

Code of Font Awesome (Icons: CC BY 4.0) is used. Licence is in this repo:

https://github.com/FortAwesome/Font-Awesome

### QuillEditor

Code of quilljs is copied into this repository.
License (BSD-3-Clause) is in this repo:

https://github.com/quilljs/quill

### Naucon/HtmlBuilder

Some code is based on Naucon's HtmlBuilder library. License (MIT) is in this repo:

https://github.com/naucon/HtmlBuilder
