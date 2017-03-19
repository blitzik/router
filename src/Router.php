<?php declare(strict_types=1);

namespace blitzik\Router;

use blitzik\Router\LocalesRouter\ILocalesLoader;
use blitzik\Router\RoutesLoader\IRoutesLoader;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Utils\Strings;
use Nette\SmartObject;
use Nette\Http\Url;

class Router implements IRouter
{
    use SmartObject;


    public $onUrlNotFound;


    const ROUTING_NAMESPACE = 'blitzik.routing';


    /** @var ILocalesLoader */
    private $localesLoader;

    /** @var IRoutesLoader */
    private $routesLoader;


    /** @var string|null */
    private $filesExtension = null;

    /** @var bool */
    private $isSecure = false;


    public function __construct(
        IRoutesLoader $routesLoader,
        ILocalesLoader $localesLoader
    ) {
        $this->routesLoader = $routesLoader;
        $this->localesLoader = $localesLoader;
    }


    public function setAsSecured(bool $secured)
    {
        $this->isSecure = $secured;
    }


    public function setFilesExtension(string $fileExtension = null)
    {
        $this->filesExtension = $fileExtension;
    }


    /**
     * @param IRequest $httpRequest
     * @return Request|NULL
     */
    public function match(IRequest $httpRequest)
    {
        $url = $httpRequest->getUrl();
        $basePath = $url->getPath();

        $path = mb_substr($basePath, \mb_strlen($url->getBasePath()));
        if ($path !== '') {
            $path = rtrim(rawurldecode($path), '/');
        }
        $path = preg_replace('~' . preg_quote('.' . $this->filesExtension, '~') . '$~', '', $path);

        $locales = $this->localesLoader->loadLocales();
        $locale = null;
        if (!empty($locales)) {
            $localesRegexp = sprintf('~^(%s)/?~', implode('|', $locales));
            if (preg_match($localesRegexp, $path, $matches)) {
                $locale = $matches[1];
            } else {
                $locale = $this->localesLoader->getDefaultLocale();
            }
        }

        $urlEntity = $this->routesLoader->loadUrlByPath($path);
        if ($urlEntity === null) {
            $this->onUrlNotFound($path);
            return null;
        }

        if ($urlEntity->getUrlToRedirect() === null) {
            $presenter = $urlEntity->getPresenter();
            $internal_id = $urlEntity->getInternalId();
            $action = $urlEntity->getAction();
        } else {
            $presenter = $urlEntity->getUrlToRedirect()->getPresenter();
            $internal_id = $urlEntity->getUrlToRedirect()->getInternalId();
            $action = $urlEntity->getUrlToRedirect()->getAction();
        }

        $params = [];
        foreach ($urlEntity->getParameters() as $name => $value) {
            $params[$name] = $value;
        }

        $params = $httpRequest->getQuery() + $params;
        $params['action'] = $action;

        if ($locale !== null) {
            $params['locale'] = $locale;
        }

        if ($internal_id !== null) {
            $params['internalId'] = $internal_id;
        }

        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles()
        );
    }


    /**
     * @param Request $appRequest
     * @param Url $refUrl
     * @return string|NULL
     */
    public function constructUrl(Request $appRequest, Url $refUrl)
    {
        $urlEntity = $this->routesLoader
                          ->loadUrlByDestination(
                              $appRequest->getPresenterName(),
                              $appRequest->getParameter('action'),
                              $appRequest->getParameter('internalId')
                          );

        if ($urlEntity === null) {
            return null;
        }

        $baseUrl = sprintf(
            '%s%s%s',
            ($this->isSecure ? 'https://' : 'http://'),
            $refUrl->getAuthority(),
            $refUrl->getBasePath()
        );

        if ($urlEntity->getUrlToRedirect() === null) {
            $path = $urlEntity->getUrlPath();
        } else {
            $path = $urlEntity->getUrlToRedirect()->getUrlPath();
        }

        $resultPath = sprintf(
            '%s%s%s',
            $baseUrl,
            Strings::webalize($path, '/.'),
            ($this->filesExtension !== null ? '.' . $this->filesExtension : null)
        );

        $params = $appRequest->getParameters();
        unset($params['action'], $params['locale'], $params['internalId']);
        foreach (array_keys($urlEntity->getParameters()) as $paramName) {
            unset($params[$paramName]);
        }

        $q = http_build_query($params, '', '&');
        if ($q != '') {
            $resultPath .= '?' . $q;
        }

        return $resultPath;
    }

}