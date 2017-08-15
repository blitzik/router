blitzik Router
===

Router with basic loaders is useful for small websites with a few pages.
You just define your url addresses in Neon file and the Router will
take care of the rest.

Installation
---

<pre>$ composer require blitzik/router</pre>

Configuration
---

So you've downloaded the extension and now you have to register it in your
configuration file:

**config.neon**
```neon
extensions:
	router: blitzik\Router\DI\RouterExtension
```

Extension is now registered under the name **router**.
Let's add the Router into RouteList.

Find your **RouterFactory.php** and modify it:

```php
class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(Nette\Application\IRouter $customRouter)
    {
        $router = new RouteList;
        
        $router[] = $customRouter;
        $router[] = new Route('<presenter>/<action>', 'YourPresenter:default'); 

        return $router;
    }

}
```

Add router as a service in **config.neon**:

```neon
services:
	router: App\RouterFactory::createRouter(@router.router)
```

Router is ready so let's create some urls. Default location for your
routing file is app/router/routing.neon. The location can be changed
in config file:

**config.neon**
```neon
router: # this is the name of registered extension
	routingFile: %appDir%/your/file/path/routing.neon
```

Urls definition
---

Urls are defined under **paths** section.

Each path can point to it's own presenter.

**routing.neon**
```neon
paths:	
	# empty string means main page
	"": Homepage:default        # example.com
	"about": About:default      # example.com/about
	"contact": Contact:default  # example.com/contact
```


Or many paths can point to exactly one presenter. 

```neon
paths:	
	# empty string means main page
	"": Homepage:default    # example.com
	"about": Page:default   # example.com/about
	"contact": Page:default # example.com/contact
```

For each url is created identifier that can be used in presenter to load
desired page. Identifier is created from url path. Basically
the alphabet characters that follows / (forward slash) or - (dash) are 
made uppercase and the forward slashes and dashes are removed.

Some examples of identifiers:

```neon
paths:	
	                                       # Identifier
	"": Homepage:default                   # ""
	"page": Page:default                   # page
	"page2.html": Page:default             # page2.html
	"page-name": Page:default              # pageName
	"page-1name": Page:default             # page1name
	"en/pagename": Page:default            # enPagename
	"en/page-name": Page:default           # enPageName
	"en/category/page-name": Page:default  # enCategoryPageName
	
```

You can then create templates, name them after identifiers and process
them:

```text
app
--- presenters
------ templates
--------- Page
------------ page.latte (example.com/page)
------------ page2.html.latte (example.com/page2.html)            
------------ pageName.latte (example.com/page-name)
etc.
```

```php
class PagePresenter extends Nette\Application\UI\Presenter
{
    // $internalId parameter is the identifier
    public function actionDefault($internalId)
    {
        $this->setView($internalId);
        
        // or load page from database if you write your own url loader
    }
}
```

If you don't like automatically created identifiers, you can set your
own:

**routing.neon**

```neon
paths:
	"": Homepage:default
	"about":
		destination: Page:default
		internalId: aboutPage # your identifier
	    
	"contact":
		destination: Page:default
		internalId: contactPage # your identifier
```

If you need a parameter or more, you can define internal parameters
under **internalParameters** section:

```neon
paths:
	"cool-page/january":
		destination: Page:default
		internalParameters:
			month: 1

	"cool-page/february":
		destination: Page:default
		internalParameters:
			month: 2
```

```php
class PagePresenter extends Nette\Application\UI\Presenter
{
    // and here you will get the internal parameter/s
    public function actionDefault($internalId, $month)
    {
        
    }
}
```

**Cache needs to be cleared after every change in your routing file.**


Redirection
---

Router supports simple redirection from one url to other:

```neon
paths:
	"": Homepage:default
	"page":
		destination: Page:default
		redirectTo: different-page
		
	"different-page": Page:default
```

Locales
---

When you are building multilingual website, you need to know what locale is
currently set.

You have to specify locales section in **routing.neon**:

```neon
locales:
	- cs
	- en
	- de

paths:
	"": Homepage:default	# cs
	"stranka": Page:default # cs
	"en/page": Page:default # en
```

When you define your locales like this then the first locale in list will be
the default one. If you want to specify explicitly your default locale, you can
use word **default** as a key.

```neon
locales:
	- cs
	default: en
	- de

paths:
	"": Homepage:default	   # en
	"page": Page:default       # en
	"cs/stranka": Page:default # cs
```

Locale **have to be specified at the start of the URL path** so Router can
take this locale and create a parameter that will be passed into application.
Only locales that are set in list can be turned into parameter.


Router settings
---

An extension (.html, ...) for each URL can be set in your routing file but
there is better solution if you want to set it globally:

**config.neon**

```neon
router:
	routingFile: %appDir%/your/file/path/routing.neon
	extension: html
```

If your website is secured with SSL, you can turn on an option that will
reflect that into your urls:

```neon
router:
	routingFile: %appDir%/your/file/path/routing.neon
	isSecured: true
```