<?php declare(strict_types=1);

namespace blitzik\Router;

use blitzik\Router\Exceptions\ParameterFilterAlreadySet;
use blitzik\Router\ParameterFilters\IParameterFilter;
use blitzik\Router\LocalesLoader\ILocalesLoader;
use blitzik\Router\RoutesLoader\IRoutesLoader;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\SmartObject;

class Router implements IRouter
{
    use SmartObject;


    public $onUrlNotFound;


    const ROUTING_NAMESPACE = 'blitzik.routing';


    /** @var array */
    private $parameterFilters = [];

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


    public function setAsSecured(bool $secured): void
    {
        $this->isSecure = $secured;
    }


    public function setFilesExtension(string $fileExtension = null): void
    {
        $this->filesExtension = $fileExtension;
    }


    public function addParameterFilter(IParameterFilter $parameterFilter)
    {
        if (isset($this->parameterFilters[$parameterFilter->getName()])) {
            throw new ParameterFilterAlreadySet(sprintf('Parameter Filter "%s" already set. You have more Parameter Filters with same name.', $parameterFilter->getName()));
        }
        $this->parameterFilters[$parameterFilter->getName()] = $parameterFilter;
    }


    public function match(IRequest $httpRequest): ?Request
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
        foreach ($urlEntity->getInternalParameters() as $name => $value) {
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

        $this->modifyParameters($params, $urlEntity, IParameterFilter::FILTER_IN);

        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles()
        );
    }


    public function constructUrl(Request $appRequest, \Nette\Http\Url $refUrl): ?string
    {
        $urlEntity = $this->routesLoader
                          ->loadUrlByDestination(
                              $appRequest->getPresenterName(),
                              $appRequest->getParameter('action'),
                              $appRequest->getParameter('internalId')
                          );

        if ($urlEntity === null or $urlEntity->isOneWay()) {
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
            $path,
            ($this->filesExtension !== null ? '.' . $this->filesExtension : null)
        );

        $params = $appRequest->getParameters();
        unset($params['action'], $params['locale'], $params['internalId']);
        foreach (array_keys($urlEntity->getInternalParameters()) as $paramName) {
            unset($params[$paramName]);
        }

        $this->modifyParameters($params, $urlEntity, IParameterFilter::FILTER_OUT);

        $q = http_build_query($params, '', '&');
        if ($q != '') {
            $resultPath .= '?' . $q;
        }

        return $resultPath;
    }


    private function modifyParameters(array &$params, Url $url, string $filterType): void
    {
        foreach ($params as $parameterName => $value) {
            $filterName = $url->getFilterByParameterName($parameterName);
            if ($filterName === null) {
                continue;
            }

            if (!isset($this->parameterFilters[$filterName])) {
                continue;
            }

            $filter = $this->parameterFilters[$filterName];
            if ($filterType === IParameterFilter::FILTER_IN) {
                $params[$parameterName] = $filter->filterIn($params[$parameterName]);
            }

            if ($filterType === IParameterFilter::FILTER_OUT) {
                $params[$parameterName] = $filter->filterOut($params[$parameterName]);
            }
        }
    }

}