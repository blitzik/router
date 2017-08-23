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
Our new router should be working at this time.

If you want to combine power of this router with
Nette's native routes, we can add our new Router
into RouteList.

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

Edit **config.neon**:

```neon
services:
	router.router:
		autowired: no

	myRouter: App\RouterFactory::createRouter(@router.router)
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

**routing.neon** (auto IDs are disabled)
```neon
paths:	
	# empty string means main page
	"": Homepage:default        # example.com
	"about": About:default      # example.com/about
	"contact": Contact:default  # example.com/contact
```

If you enable automatically generated internal IDs or set your own internal IDs,
then many paths can point to exactly one presenter.<br>
(How to enable auto internal IDs can be found in Router Settings section at the bottom of this page)

You can write your urls this way if auto internal IDs are enabled. You don't have
to specify internal IDs for routes pointing to same presenter.

**routing.neon** (auto IDs are enabled)
```neon
paths:	 
	"": Homepage:default    # example.com
	"about": Page:default   # example.com/about
	"contact": Page:default # example.com/contact	
```

On the other hand, if auto generated internal IDs are disabled and you have some urls
pointing to same presenter, you have to specify internal IDs manually otherwise 
an exception will be thrown.

**routing.neon** (auto IDs are disabled)
```neon
paths:
    "": Homepage:default # example.com 
    "about": # example.com/about
        destination: Page:default
        internalId: about
        
	   	
    "contact": # example.com/contact
        destination: Page:default
        internalId: contact
```

Some examples of generated identifiers:

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

So, let's say we have auto generated internal IDs enabled or we have many paths pointing
to one presenter and each path has manually set internal ID. 
We can then create templates, name them after identifiers and process them:

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

**routing.neon** (auto IDs are enabled)

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

Apart from internal IDs you can set an internal parameter or more under 
**internalParameters** section:

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


If you have an old Url (and that Url does not have set Presenter and Action) that
you want to redirect to a new one but you don't want
to create links on this old one url, you can set it as one way url:

```neon
paths:
	"": Homepage:default
	"old-page":
		oneWay: different-page
		
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


Parameter filters
---

You can add to router a parameter filter that will modify a value of a parameter in
url's query string.

Example: If you want to hide real, integer IDs in url.

We have to create a parameter filter first, so let's create a class that
implements interface **IParameterFilter**.

```php
class PageIdFilter implements IParameterFilter
{
    // Method returns name of the filter
    // (must be unique otherwise an exception will be thrown)
    public function getName(): string
    {
        return 'PageIdFilter';
    }


    // method that have to "decode" encoded parameter
    public function filterIn($modifiedParameter): ?string
    {
        return (string)hexdec($modifiedParameter);
    }


    // method that have to "encode" parameter
    public function filterOut($parameter): ?string
    {
        return (string)dechex($parameter);
    }
}
```

So, we've created our parameter filter and we have to register it in our
config file as a service.
Router extension will find this service and automatically adds it into the Router.

**config.neon**
```neon
services:
	- PageIdFilter
```

Parameter filter is now ready to use. Each path in routing file can have "filters"
section where we can specify which parameters should be affected by our filters.

**routing.neon** (auto IDs are disabled)
```neon
paths:
	"": Homepage:default
	
	products:
		destination: Product:overview
		filters:
			PageIdFilter: # our created filter
				- id  # name of affected parameter
```


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

**config.neon**

```neon
router:
	routingFile: %appDir%/your/file/path/routing.neon
	isSecured: true
```

If you want the Router to automatically create internalIds to your routes,
you can enable this function.

**config.neon**

```neon
router:
	autoInternalIds: true # by default it's FALSE
```